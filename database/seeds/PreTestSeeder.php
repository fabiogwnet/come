<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dirInsertsDefault = __DIR__ . '/../inserts';
        $files = [];

        try {
            DB::beginTransaction();

            foreach (new DirectoryIterator($dirInsertsDefault) as $fileInfo) {
                $fileInfo->getFilename();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        DB::connection('testing')->statement();
    }
}
