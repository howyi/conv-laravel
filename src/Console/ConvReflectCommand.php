<?php

namespace Conv\Laravel\Console;

use Conv\Operator;
use Conv\Structure\TableStructureInterface;
use Illuminate\Console\Command;
use Conv\CreateQueryReflector;

class ConvReflectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conv:reflect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reflect DB schema to directory';

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
        $operator = new Operator(
            $this->getHelper('question'),
            $this->input,
            $this->output
        );
        $filter = function (TableStructureInterface $table) {
            return !in_array($table->getName(), config('conv.ignore_tables', []), true);
        };
        CreateQueryReflector::fromPDO(
            \DB::connection()->getPdo(),
            \DB::getDatabaseName(),
            config('conv.paths.schemas', 'database/schemas'),
            $operator,
            $filter
        );
    }
}
