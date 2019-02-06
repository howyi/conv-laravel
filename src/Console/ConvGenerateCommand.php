<?php

namespace Howyi\ConvLaravel\Console;

use Howyi\Conv\DatabaseStructureFactory;
use Howyi\Conv\Migration\Table\TableMigrationInterface;
use Howyi\Conv\MigrationGenerator;
use Howyi\Conv\MigrationType;
use Howyi\Conv\Operator\ConsoleOperator;
use Howyi\Conv\Structure\TableStructureInterface;
use Illuminate\Console\Command;

class ConvGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conv:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-generate migration from schema';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('migrate');

        $filter = function (TableStructureInterface $table) {
            return !in_array($table->getName(), config('conv.ignore_tables', []), true);
        };

        $pdo = \DB::connection()->getPdo();
        $operator = new ConsoleOperator(
            $this->getHelper('question'),
            $this->input,
            $this->output
        );
        $schemaDbStructure = DatabaseStructureFactory::fromSqlDir(
            $pdo,
            config('conv.paths.schemas', 'database/schemas'),
            $operator,
            $filter
        );
        $dbStructure = DatabaseStructureFactory::fromPDO($pdo, \DB::getDatabaseName(), $filter);

        $alterMigrations = MigrationGenerator::generate(
            $dbStructure,
            $schemaDbStructure,
            $operator
        );

        $generatedContents = [];
        $i = (new \GlobIterator('./' . config('conv.paths.migrations', 'database/migrations') . '/*.php'))->count();
        foreach ($alterMigrations->getMigrationList() as $migration) {
            $migrationName = $this->getMigrationName($migration);
            $fileName = date('Y_m_d_His_') . $migrationName . "_$i";

            $migration->getType();

            // pascalize
            $className = strtolower($migrationName);
            $className = str_replace('_', ' ', $className);
            $className = ucwords($className);
            $className = str_replace(' ', '', $className);
            $className .= $i;

            $up = $migration->getUp();
            $down = $migration->getDown();
            $content = <<<EOL
<?php

use Illuminate\Database\Migrations\Migration;

class $className extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \$sql = <<<SQL
$up
SQL;
        DB::statement(\$sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \$sql = <<<SQL
$down
SQL;
        DB::statement(\$sql);
    }
}


EOL;
            $generatedContents["$fileName.php"] = $content;
            $i++;
        }

        $operator->output("\n");

        if (0 !== count($alterMigrations->getMigrationList())) {

            foreach ($alterMigrations->getMigrationList() as $migration) {
                $operator->output("<fg=green>### TABLE NAME: {$migration->getTableName()}</>");
                $operator->output('<fg=yellow>--------- UP ---------</>');
                $operator->output("<fg=blue>{$migration->getUp()}</>");
                $operator->output('<fg=yellow>-------- DOWN --------</>');
                $operator->output("<fg=magenta>{$migration->getDown()}</>\n\n");
            }
        }

        $count = count($alterMigrations->getMigrationList());
        $operator->output("<fg=green>Generated $count migrations</>");

        if (0 !== count($generatedContents)) {
            foreach ($generatedContents as $filename => $content) {
                file_put_contents("./" . config('conv.paths.migrations', 'database/migrations') . "/$filename", $content);
            }
        }
    }

    public function getMigrationName(TableMigrationInterface $migration): string
    {
        $tableName = $migration->getTableName();
        switch ($migration->getType()){
            case MigrationType::CREATE:
            case MigrationType::VIEW_CREATE:
                return "create_$tableName";
            case MigrationType::ALTER:
                return "alter_$tableName";
            case MigrationType::DROP:
            case MigrationType::VIEW_DROP:
                return "drop_$tableName";
            case MigrationType::CREATE_OR_REPLACE:
                return "create_or_replace_$tableName";
            case MigrationType::VIEW_RENAME:
                return "rename_$tableName";
        }
    }
}
