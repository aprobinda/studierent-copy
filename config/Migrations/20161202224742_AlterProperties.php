<?php
use Migrations\AbstractMigration;

class AlterProperties extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('properties');
        $table->changeColumn('contact_number', 'string', [
            'default' => null,
            'limit' => 30,
            'null' => true,
        ]);
        $table->update();
    }
}
