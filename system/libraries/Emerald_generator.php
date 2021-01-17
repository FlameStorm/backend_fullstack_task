<?php
defined('TAB1') or define('TAB1', "\t");

class CI_Emerald_generator {


    const DATA_TYPE = [
        'int' => [
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'bigint'
        ],
        'float' => [
            'decimal',
            'float',
            'double',
            'real',
        ],
//		'string' => [
//			'enum',
//			'set',
//			'varchar',
//			'char',
//			'datetime',
//		],
    ];

    private static $db_config;

    private static $db_table_schema;

    protected static $file_content;

    public function __construct()
    {
        App::get_ci()->load->helper('file');
    }

    public static function generate_model(string $class_name, string $extends = 'MY_Model')
    {
        self::$file_content = NULL;
        self::load_db_config();

        self::$db_table_schema = App::get_ci()->s->sql(sprintf('
		SELECT *
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_SCHEMA = "%s"
		AND TABLE_NAME = "%s"
		ORDER BY `ORDINAL_POSITION` ASC		
		', self::get_db_config('database'), $class_name))->many();


        self::file_generate_header($class_name, $extends);

        self::file_generate_content();

        self::file_generate_footer($class_name);

        $result_path = self::create_file($class_name);

        echo 'Model generated! Check path: ' . $result_path;
    }

    protected static function detect_data_type(string $data_type): string
    {
        foreach (self::DATA_TYPE as $key => $types)
        {
            if (in_array($data_type, $types))
            {
                return $key;
            }
        }
        return 'string';
    }

    protected static function file_generate_header(string $class_name, string $extends = 'MY_Model')
    {
        self::$file_content = '<?php ' . PHP_EOL;
        self::$file_content .= 'class ' . ucfirst($class_name) . '_model';
        if ( ! empty($extends))
        {
            self::$file_content .= ' extends ' . $extends . PHP_EOL;
        } else
        {
            self::$file_content .= PHP_EOL;
        }
        self::$file_content .= '{' . PHP_EOL;
        self::$file_content .= PHP_EOL;

        self::$file_content .= TAB1 . sprintf('const CLASS_TABLE = \'%s\';', $class_name) . PHP_EOL;
        self::$file_content .= PHP_EOL;
    }

    protected static function file_generate_content()
    {
        foreach (self::$db_table_schema as $idx => $col)
        {
            //var_dump($col);
            if ($col['COLUMN_NAME'] == 'id')
            { //IS_NULLABLE
                continue;
            }
            $phpdoc = '/** @var ';

            $phpdoc .= ' ' . self::detect_data_type($col['DATA_TYPE']);

            if ($col['IS_NULLABLE'] == 'YES')
            {
                $phpdoc .= '|null ';
            }

            //var_dump($col['COLUMN_NAME']);
            //var_dump($col['DATA_TYPE']);

            $phpdoc .= '*/';

            self::$file_content .= TAB1 . $phpdoc . PHP_EOL;
            self::$file_content .= TAB1 . 'protected $' . $col['COLUMN_NAME'] . ';' . PHP_EOL;
        }
    }

    protected static function file_generate_footer(string $class_name)
    {

        self::$file_content .= PHP_EOL;
        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * ' . ucfirst($class_name) . '_model constructor.' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @param int|null $id' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'function __construct($id = NULL)' . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . 'parent::__construct();' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . '$this->set_id($id);' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;
        self::$file_content .= PHP_EOL;


        self::$file_content .= PHP_EOL;
        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @param bool $for_update' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @return ' . ucfirst($class_name) . '_model' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'public function reload(bool $for_update = FALSE)' . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . 'parent::reload($for_update);' . PHP_EOL;
        self::$file_content .= TAB1 . TAB1 . 'return $this;' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;
        self::$file_content .= PHP_EOL;


        self::$file_content .= PHP_EOL;

        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @param array $data' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @return static' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'public static function create(array $data)' . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . '	App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();' . PHP_EOL;
        self::$file_content .= TAB1 . '	return new static(App::get_ci()->s->get_insert_id());' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;

        self::$file_content .= TAB1 . '/**' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @return bool' . PHP_EOL;
        self::$file_content .= TAB1 . ' * @throws Exception' . PHP_EOL;
        self::$file_content .= TAB1 . ' */' . PHP_EOL;
        self::$file_content .= TAB1 . 'public function delete()' . PHP_EOL;
        self::$file_content .= TAB1 . '{' . PHP_EOL;
        self::$file_content .= TAB1 . '	$this->is_loaded(TRUE);' . PHP_EOL;
        self::$file_content .= TAB1 . '	App::get_ci()->s->from(self::get_table())->where([\'id\' => $this->get_id()])->delete()->execute();' . PHP_EOL;
        self::$file_content .= TAB1 . '	return App::get_ci()->s->get_affected_rows() > 0;' . PHP_EOL;
        self::$file_content .= TAB1 . '}' . PHP_EOL;
        self::$file_content .= PHP_EOL;

        self::$file_content .= '}' . PHP_EOL;
    }


    protected static function create_file(string $file_name): string
    {
        $path = self::get_ci_cache_path() . ucfirst($file_name) . '_model.php';
        write_file($path, self::$file_content);
        return $path;
    }

    private static function get_ci_cache_path(): string
    {
        $path = APPPATH . 'cache/';
        return $path;
    }


    protected static function get_db_config(string $key)
    {
        if (empty(self::$db_config['default']))
        {
            throw new CriticalException('Cant get db config! Please load it firts!');
        }
        return self::$db_config['default'][$key];
    }


    private static function load_db_config(): bool
    {
        self::$db_config = [];

        $file = 'database';
        foreach (find_config_files_path($file) as $location)
        {
            $file_path = APPPATH . 'config/' . $location . '.php';

//                log_message('error', '['.PROJECT.']Config try search: '.$file_path);

            if ( ! file_exists($file_path))
            {
                continue;
            }

            include($file_path);

            if ( ! isset($db) or ! is_array($db))
            {
                show_error('Your ' . $file_path . ' file does not appear to contain a valid configuration array.');
            }


            $loaded = TRUE;
            log_message('debug', 'Config file loaded: ' . $file_path);
        }

        if ( ! $loaded)
        {
            show_error('Cant find ' . $file . '.php config file!');
        }

        self::$db_config = $db;

        return TRUE;
    }


}