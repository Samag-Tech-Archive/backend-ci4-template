<?php namespace SamagTech\Log\Database\Migrations;


/**
 * CodeIgniter Logger
 *
 * @package Log
 * @author  Alessandro Marotta <alessandro.marotta@samag.tech>
 */
class Migration_Log extends \CodeIgniter\Database\Migration {
    
    
    public function up() {
        
        $config = config('Log');
        
        $fields = [
            'id'    =>  [
                'type'              => 'INT',
                'constraint'        => 22,
                'unsigned'          => true,
				'auto_increment'    => true,
            ],
            'table' =>  [
                'type'          =>  'VARCHAR',
                'constraint'    =>  '50',
                'null'          => false
            ],
            'row_id' =>  [
                'type'          =>  'INT',
                'constraint'    =>  '11',
                'unsigned'      => true,
                'null'          => false
            ],
            'service' => [
                'type'          => 'VARCHAR',
                'constraint'    => 255,
                'null'          => false
            ],
            'old_data'  =>  [
                'type'          => 'JSON',
                'null'          => false
            ],
            'new_data'  =>  [
                'type'          => 'JSON',
                'null'          => true
            ],
            'type'      =>  [
                'type'          => 'ENUM',
                'constraint'    =>  $config->typeLogs
            ],
            'user'   =>  [
                'type'          => 'JSON',
                'null'          => true
            ],
            'created_date DATETIME DEFAULT now()'
        ];

        $this->forge->addPrimaryKey('id');
        $this->forge->addField($fields);
        $this->forge->createTable($config->table);
    }

    public function down() {
        
        $config = config('Log');

        $this->forge->dropTable($config->table);
    }
}