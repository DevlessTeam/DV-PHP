<?php

namespace Devless\Schema;

use App\Helpers\Helper;
use App\Helpers\Response as Response;
use App\Http\Controllers\ServiceController as Service;
use Illuminate\Database\Schema\Blueprint as Blueprint;
use Illuminate\Http\Request;

class DbHandler
{
    public $db_types = [
        'text'       => 'string',
        'textarea'   => 'longText',
        'integer'    => 'integer',
        'decimals'   => 'double',
        'password'   => 'string',
        'percentage' => 'integer',
        'url'        => 'string',
        'timestamp'  => 'timestamp',
        'boolean'    => 'boolean',
        'email'      => 'string',
        'reference'  => 'integer',
    ];

    public $query_params = [
        'order'    => 'orderBy',
        'where'    => 'where',
        'take'     => 'take',
        'relation' => 'relation',
    ];

    /*
    * access db functions based on request method type
    * @param string resource name $resource
    * @param array payload $payload
    */
    public function access_db($resource, $payload)
    {
        $payload['user_id'] = '';

        if ($payload['method'] == 'GET') {
            $db_action = 'query';
            $payload = $this->set_auth_id_if_required($db_action, $payload);

            return $this->db_query($resource, $payload);
        } elseif ($payload['method'] == 'POST') {
            $db_action = 'create';
            $payload = $this->set_auth_id_if_required($db_action, $payload);

            return $this->add_data($resource, $payload);
        } elseif ($payload['method'] == 'PATCH') {
            $db_action = 'update';
            $payload = $this->set_auth_id_if_required($db_action, $payload);

            return $this->update($resource, $payload);
        } elseif ($payload['method'] == 'DELETE') {
            $db_action = 'delete';
            $payload = $this->set_auth_id_if_required($db_action, $payload);

            return $this->destroy($resource, $payload);
        } else {
            Helper::interrupt(607);
        }
    }

    /**
     * create new table schema .
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     *                                   api/v1/schema
     */
    public function store(Request $request)
    {
        //
        $resource = 'schema';

        $this->create_schema($resource, $request['resource']);
    }

    /**
     * query for data from db.
     *
     * @param string $resource
     *
     * @return \Illuminate\Http\Response
     */
    public function add_data($resource, $payload)
    {
        $service_id = $payload['id'];
        $service_name = $payload['service_name'];

        //setup db connection
        $connector = $this->_connector($payload);

        $db = \DB::connection('DYNAMIC_DB_CONFIG');
        foreach ($payload['params'] as $table) {
            $table_name = $table['name'];
            if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($service_name.'_'.$table_name)) {
                Helper::interrupt(634);
            }
            //check data against field type before adding data
            $table_data = $this->_validate_fields(
                $table_name,
                $service_name,
                $table['field'],
                true
            );

            //assigning autheticated user id
            $table_data[0]['devless_user_id'] = $payload['user_id'];
            $output = $db->table($service_name.'_'.$table['name'])->insert($table_data);
        }

        if ($output) {
            return Response::respond(609, 'Data has been added to '.$table['name']
            .' table succefully');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $resource
     * @param array  $payload  payload
     *
     * @return
     */
    public function update($resource, $payload)
    {
        $connector = $this->_connector($payload);
        $db = \DB::connection('DYNAMIC_DB_CONFIG');
        $service_name = $payload['service_name'];

        if (isset(
            $payload['params'][0]['name'],
            $payload['params'][0]['params'][0]['where'],
            $payload['params'][0]['params'][0]['data']
        )) {
            $table_name = $service_name.'_'.$payload['params'][0]['name'];

            if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($table_name)) {
                return Helper::interrupt(634);
            }

            $where = $payload['params'][0]['params'][0]['where'];
            $explotion = explode(',', $where);
            $data = $payload['params'][0]['params'][0]['data'];

            if ($payload['user_id'] !== '') {
                $result = $db->table($table_name)
                ->where($explotion[0], $explotion[1])
                ->where('devless_user_id', $payload['user_id'])
                ->update($data[0]);
            } else {
                $result = $db->table($table_name)
                ->where($explotion[0], $explotion[1])
                ->update($data[0]);
            }

            if ($result == 1) {
                return Helper::interrupt(
                    619,
                    'table '.$payload['params'][0]['name'].' updated successfuly'
                );
            } else {
                Helper::interrupt(629, 'Table '.$payload['params'][0]['name'].' could not be updated');
            }
        } else {
            Helper::interrupt(614);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $resource
     * @param array  $payload  payload from request
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($resource, $payload)
    {
        $connector = $this->_connector($payload);
        $db = \DB::connection('DYNAMIC_DB_CONFIG');
        //check if table name is set
        $service_name = $payload['service_name'];
        $ORG_table_name = $payload['params'][0]['name'];
        $table_name = $service_name.'_'.$ORG_table_name;

        if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
        hasTable($table_name)) {
            Helper::interrupt(634);
        }
        if ($payload['user_id'] !== '') {
            $user_id = $payload['user_id'];
            $destroy_query = '$db->table("'.$table_name.'")->where("devless_user_id",'.$user_id.')';
        } else {
            $destroy_query = '$db->table("'.$table_name.'")';
        }

        if (isset($payload['params'][0]['params'][0]['drop'])) {
            if ($payload['params'][0]['params'][0]['drop'] == true) {
                \Schema::connection('DYNAMIC_DB_CONFIG')->dropIfExists($table_name);
                \DB::table('table_metas')->where('table_name', $ORG_table_name)->delete();

                return Response::respond(613, 'dropped table successfully');
                $task = 'drop';
            }
        }
        if (isset($payload['params'][0]['params'][0]['where'])) {
            if ($payload['params'][0]['params'][0]['where'] == true) {
                $where = $payload['params'][0]['params'][0]['where'];
                $where = str_replace(',', "','", $where);
                $where = "'".$where."'";
                $destroy_query = $destroy_query.'->where('.$where.')';
                $task = 'failed';
            }
        }
        $element = 'row';
        if (isset($payload['params'][0]['params'][0]['truncate'])) {
            if ($payload['params'][0]['params'][0]['truncate'] == true) {
                $destroy_query = $destroy_query.'->truncate()';
                $tasked = 'truncated';
                $task = 'truncate';
            }
        } elseif (isset($payload['params'][0]['params'][0]['delete'])) {
            if ($payload['params'][0]['params'][0]['delete'] == true) {
                $destroy_query = $destroy_query.'->delete()';
                $tasked = 'deleted';
                $task = 'delete';
            }
        } else {
            Helper::interrupt(615);
        }

        $destroy_query = $destroy_query.';';
        $result = eval('return'.$destroy_query);
        if ($result == false && $result != null) {
            Helper::interrupt(614, 'could not '.$task.' '.$element);
        }

        return Response::respond(636, 'The table or field has been '.$task);
    }

    /**
     * query a table.
     *
     * @param string $resource
     * @param array  $payload  payload from request
     *
     * @return \Illuminate\Http\Response
     */
    public function db_query($resource, $payload)
    {
        $service_name = $payload['service_name'];
        $connector = $this->_connector($payload);
        $db = \DB::connection('DYNAMIC_DB_CONFIG');
        $results = [];
        //check if table name is set
        if (isset($payload['params']['table'][0])) {
            if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($service_name.'_'.$payload['params']['table'][0])) {
                return Helper::interrupt(634);
            }
            if ($payload['user_id'] !== '') {
                $user_id = $payload['user_id'];
                $base_query = '$db->table("'.$service_name.'_'.$payload['params']['table'][0].'")'
                .'->where("devless_user_id",'.$user_id.')';
            } else {
                $base_query = '$db->table("'.$service_name.'_'.$payload['params']['table'][0].'")';
            }
            $table_name = $service_name.'_'.$payload['params']['table'][0];

            (isset($payload['params']['offset'])) ?
            $complete_query = $base_query
            .'->skip('.$payload['params']['offset'][0].')' :
            false;

            (isset($payload['params']['size'])) ?
            $complete_query = $complete_query
            .'->take('.$payload['params']['size'][0].')' :

            $complete_query = $base_query;


            (isset($payload['params']['related'])) ? $queried_table_list =
            $payload['params']['related'] : false;


            unset($payload['params']['related']);
            $related = [];


            if (isset($payload['params']['order'])) {
                $order_by = $payload['params']['order'];
                $complete_query = $complete_query
                .'->orderBy("'.$payload['params']['order'][0].'" )';
                unset($payload['params']['order']);
            }
            unset(
                $payload['params']['table'],
                $payload['params']['size'],
                $payload['params']['offset']
            );
            foreach ($payload['params'] as $key => $query) {
                foreach ($query as $one) {
                    //prepare query for order and where
                    if (isset($this->query_params[$key])) {
                        $query_params = explode(',', $one);

                        if (isset($query_params[1], $query_params[0])) {
                            $complete_query = $complete_query.
                            '->'.$this->query_params[$key].'("'.$query_params[0].
                            '","'.$query_params[1].'")';
                        } else {
                            Helper::interrupt(612);
                        }
                    } else {
                        Helper::interrupt(610);
                    }
                }
            }
            $complete_query = 'return '.$complete_query.'->get();';
            $count = $db->table($table_name)->count();
            $query_output = eval($complete_query);
            $results['properties']['count'] = $count;
            if (count($query_output) == 1 && isset($queried_table_list)) {
                $query_output = json_decode(json_encode($query_output), true);
                $wanted_relationships = $queried_table_list;
                $related = $this->_get_related_tables(
                    $payload,
                    $table_name,
                    $query_output[0],
                    $wanted_relationships,
                    $db
                );
            }
            $results['results'] = $query_output;

            $results['properties']['related'] = $related;

            return Response::respond(625, null, $results);
        } else {
            Helper::interrupt(611);
        }
    }

    /**
     *Create a service table.
     *
     * @param array resource
     * @param array $payload
     *
     * @return true
     */
    public function create_schema($resource, array $payload)
    {
        $service_name = $payload['service_name'];
        //connectors mysql pgsql sqlsrv sqlite
        $connector = $this->_connector($payload);

        //dynamically create columns with schema builder
        $db_type = $this->db_types;
        $table_meta_data = [];
        $new_payload = $payload['params'][0];
        $new_payload['id'] = $payload['id'];
        $table_name = $service_name.'_'.$new_payload['name'];
        if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
        hasTable($service_name.'_'.$table_name)) {
            \Schema::connection('DYNAMIC_DB_CONFIG')->
            create($table_name, function (Blueprint
        $table) use ($new_payload, $db_type, $service_name) {
                //default field
                $table->increments('id');
                $table->integer('devless_user_id');
                //per  field
                foreach ($new_payload['field'] as $field) {
                    $field['ref_table'] = $service_name.'_'.$field['ref_table'];
                    $field['field_type'] = strtolower($field['field_type']);
                    //checks if fieldType and references exist
                    $this->field_type_exist($field);
                    //generate columns
                    $this->column_generator($field, $table, $db_type);
                }
                //store table_meta details
            });
            $this->_set_table_meta($new_payload);

            return Response::respond(606);
        } else {
            Helper::interrupt(603, $table_name.' table already exist');
        }
    }

    /**
     *check if field exist.
     *
     * @param column fields (array) $field
     * @param table_name            $table_name
     *
     * @return true
     */
    public function field_type_exist($field)
    {

        //check if soft data type has equivalent db type
        if (!isset($this->db_types[$field['field_type']])) {
            Helper::interrupt(600, $field['field_type'].' does not exist');
        }
        if (strtolower($field['field_type']) == 'reference') {
            if (!\Schema::connection('DYNAMIC_DB_CONFIG')->
            hasTable($field['ref_table'])) {
                //
                     Helper::interrupt(601, 'referenced table '
                .$field['ref_table'].' does not exist');
            }
        }
    }

    /**
     * check column constraints.
     *
     * @param column fields (array) $field
     *
     * @return int
     */
    public function check_column_constraints($field)
    {
        //create column with default and reference
        if ($field['field_type'] == 'reference' && $field['default'] !== null) {
            return 4;
        } elseif ($field['field_type'] == 'reference' && $field['default'] == null) {
            return 3;
        } elseif ($field['field_type'] != 'reference' && $field['default'] != null) {
            return 2;
        }
        if (($field['field_type'] !== 'reference' && $field['default'] == null)) {
            return 1;
        } else {
            Helper::interrupt(602, 'Database schema could not be created');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param array  $field
     * @param object $table
     *
     * @return object
     */
    public function column_generator($field, $table, $db_type)
    {
        $column_type = $this->check_column_constraints($field);
        $unique = '';
        if ($field['is_unique'] == 'true') {
            $unique = 'unique';
        }
        if ($column_type == 4) {
            $table->$db_type[$field['field_type']]($field['ref_table'].'_id')
            ->unsigned()->$unique();
            $table->foreign($field['ref_table'].'_id')->references('id')
            ->on($field['ref_table'])->onDelete('cascade');
        } elseif ($column_type == 3) {
            $table->$db_type[$field['field_type']]($field['ref_table'].'_id')
            ->unsigned()->$unique();
            $table->foreign($field['ref_table'].'_id')->references('id')
            ->on($field['ref_table'])->default($field['default'])
            ->onDelete('cascade');
        } elseif ($column_type == 2) {
            $table->$db_type[$field['field_type']]
            ($field['name'])->default($field['default'])->onDelete('cascade')
            ->$unique();
        } elseif ($column_type == 1) {
            $table->$db_type[$field['field_type']]
            ($field['name'])->onDelete('cascade')->$unique();
        } else {
            Helper::interrupt(
                 602,
                 'For some reason database schema could not be created'
             );
        }
    }

    public function db_socket(
        $driver,
        $host,
        $database,
        $username,
        $password,
        $charset = 'utf8',
        $prefix = '',
        $collation = 'utf8_unicode_ci'
    ) {
        if ($driver == 'sqlite') {
            $database = database_path('devless-rec.sqlite3');
        }
        $conn = [
        'driver'   => $driver,
        'host'     => $host,
        'database' => $database,
        'username' => $username,
        'password' => $password,
        'charset'  => $charset,
        'prefix'   => $prefix,
        ];
        if ($driver == 'mysql') {
            $conn['collation'] = $collation;
        }
        \Config::set('database.connections.DYNAMIC_DB_CONFIG', $conn);
    }

/*
* access different database connections
*
* @param $payload request parameters
* @return boolean
*/
    private function _connector($connector_params)
    {
        $driver = $connector_params['driver'];

        //get current database else connect to remote
        if ($driver == 'default') {
            $default_database = config('database.default');
            $default_connector = config('database.connections.'.$default_database);

            $driver = $default_connector['driver'];
            if (isset($default_connector['hostname'])) {
                $hostname = $default_connector['hostname'];
            } else {
                $hostname = (isset($default_connector['host'])) ? $default_connector['host'] : false;
            }


            $username = (isset($default_connector['username'])) ? $default_connector['username'] : false;
            $password = (isset($default_connector['password'])) ? $default_connector['password'] : false;
            $database = $default_connector['database'];
        } else {
            $driver = $connector_params['driver'];
            $hostname = $connector_params['hostname'];
            $database = $connector_params['database'];
            $username = $connector_params['username'];
            $password = $connector_params['password'];
        }
        $this->db_socket($driver, $hostname, $database, $username, $password);

        return true;
    }

/*
* get related tables
* @params $table_name
* @param $payload request parameters
* @params $wanted_relationships names fo table to get
* @params $db db connection
* @return array
*/
    private function _get_related_tables(
        $payload,
        $table_name,
        $output,
        $wanted_related_tables,
        $db
    ) {
        $service_name = $payload['service_name'];
        $table_meta = $db->table('table_metas')->where('table_name', $table_name)->get();
        $table_schema = json_decode($table_meta[0]->schema);
        $table_fields = $table_schema->field;

        $related_tables = [];
        foreach ($wanted_related_tables as $each_wanted_table) {
            if ($each_wanted_table == '*') {
                //check the model and grab all relations
                foreach ($table_fields as $each_) {
                    if ($each_->ref_table !== null &&
                    $each_->ref_table !== '') {
                        $referenced_table = $each_->ref_table;
                        $indexed_referenced_table = $service_name.'_'.$referenced_table;
                        $indexed_table = $service_name.'_'.$table_name[0];
                        $referenced_id = ($output[$indexed_referenced_table.'_id']);
                        if ($payload['user_id'] !== '') {
                            $user_id = $payload['user_id'];
                            $related_tables[$referenced_table] = $db->table($indexed_referenced_table)
                            ->where('id', $referenced_id)->where('devless_user_id', $user_id)->get();
                        } else {
                            $related_tables[$referenced_table] = $db->table($indexed_referenced_table)
                            ->where('id', $referenced_id)->get();
                        }
                    }
                }
            } else {
                foreach ($table_fields as $each_) {
                    $each_referenced_field = $each_->ref_table;
                    $referenced_field = $service_name.'_'.
                    $each_referenced_field.'_id';
                    if (isset($output[$referenced_field]) &&
                    $each_referenced_field == $each_wanted_table) {
                        if ($payload['user_id'] !== '') {
                            $related_tables[$each_wanted_table] = $db->
                            table($service_name.'_'.$each_wanted_table)
                            ->where('id', $output[$referenced_field])
                            ->where('devless_user_id', $user_id)->get();
                        } else {
                            $related_tables[$each_wanted_table] = $db->
                            table($service_name.'_'.$each_wanted_table)
                            ->where('id', $output[$referenced_field])
                            ->get();
                        }
                    }
                }
            }
        }

        return $related_tables;
    }

    private function _set_table_meta($schema)
    {
        \DB::table('table_metas')->insert(['schema' => json_encode($schema),
        'table_name'                                => $schema['name'], 'service_id' => $schema['id'], ]);


        return true;
    }

/*
*Get table meta
*
* @param string  $service_id
*
* @return array
*/
    private function _get_tableMeta($table_name)
    {
        $tableMeta = \DB::table('table_metas')->
        where('table_name', $table_name)->first();
        $tableMeta = json_decode(json_encode($tableMeta), true);
        $count = 0;
        $tableMeta['schema'] = json_decode($tableMeta['schema'], true);

        return $tableMeta;
    }

/*
* Check if a connection can be made to database
* @param array $connection_params (hostname,username,password,driver,)
*
*/
    public function check_db_connection(array $connection_params)
    {
        $connector = $this->_connector($connection_params);
        //dd(\DB::connection('DYNAMIC_DB_CONFIG')->table('kofi')->delete());
        return true;
    }

/*
* mandatory db choes from system
*
*/
    private function system_schema_jobs()
    {
        $user_cred = Helper::get_authenticated_user_cred();
        $user_id = $user_cred['id'];
        $jobs =
        [
        'query'  => $user_id,
        'update' => $user_id,
        'create' => $user_id,
        'delete' => $user_id,
        'schema' => '',
        ];

        return $jobs;
    }

/*
* add user id to payload
* @param $db_action
* @param $payload
* @return $payload || boolean
*/
    private function set_auth_id_if_required($db_action, $payload)
    {
        $service = new Service();
        $access_type = $payload['resource_access_right'];
        $authentication_required =
        $access_state = $service
        ->check_resource_access_right_type($access_type[$db_action]);

        $user_cred = Helper::get_authenticated_user_cred($access_state);

        $payload['user_id'] = $user_cred['id'];


        return $payload;
    }

/*TODO:needs serious refactoring
* validate entry data against schema field type
*
* @param string $table_name
* @param string $service_id
* @param array  $field_names
* @return boolean
*/
    private function _validate_fields(
        $table_name,
        $service_name,
        $table_data,
        $check_password = false
    ) {
        $table_meta = $this->_get_tableMeta($table_name);
        $schema = $table_meta['schema'];
        $hit = 0;
        $check = 0;
        $count = 0;


        foreach ($table_data as $field_unit) {
            foreach ($field_unit as $field => $field_value) {
                foreach ($schema['field'] as $fields) {
                    if ($fields['name'] == $field) {
                        //pass field_type and value to validate
                        $err_msg =
                        Helper::field_check(
                            $field_value,
                            $fields['field_type']
                        );

                        if ($check_password == true &&
                        strtolower($fields['field_type']) == 'password') {
                            $table_data[$count]['password'] =
                            Helper::password_hash($table_data[$count]['password']);
                        }

                        if (is_object($err_msg) == true) {
                            Helper::interrupt(616, $err_msg);
                        }
                    }
                }
            }
            $count++;
        }
        $hit = 1;





        if ($hit == 0) {
            Helper::interrupt(617);
        }

        if ($check_password == 'true') {
            return $table_data;
        } else {
            return true;
        }
    }
}
