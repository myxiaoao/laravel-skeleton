<?php

namespace App\Console\Commands;

use App\Support\Inflector;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class InflectorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inflector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inflector pluralizes and singularizes English nouns.';

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
     * @return int
     */
    public function handle()
    {
        collect()
            ->pipe(function (Collection $collection) {
                // while (true) {
                //     $phrase = $this->ask('Please enter a phrase to inflect.');
                //     if (filled($phrase)) {
                //         break;
                //     }
                // }

                for (; ;) {
                    $phrase = $this->ask('Please enter a phrase to inflect.');
                    if (filled($phrase)) {
                        break;
                    }
                }

                $type = $this->choice(
                    'Please choose the inflector type',
                    $types = [
                        'all',
                        'Laravel:plural',
                        'Laravel:singular',
                        'CakePHP:pluralize',
                        'CakePHP:singularize',
                        'CakePHP:camelize',
                        'CakePHP:variable',
                        'CakePHP:classify',
                        'CakePHP:tableize',
                        'CakePHP:underscore',
                        'CakePHP:dasherize',
                        'CakePHP:humanize',
                    ],
                    'all'
                );

                $classPluck = [
                    'Laravel' => Pluralizer::class,
                    'CakePHP' => Inflector::class,
                ];

                return collect($type === 'all' ? array_slice($types, 1) : [$type])
                    ->reduce(function (Collection $results, string $type) use ($phrase, $classPluck) {
                        $result = Str::of($type)->explode(':')
                            ->pipe(function (Collection $parts) use ($phrase, $classPluck) {
                                return $classPluck[$parts->first()]::{$parts->last()}($phrase);
                            });

                        return $results->add([
                            'type' => $type,
                            'result' => $result,
                        ]);
                    }, collect());
            })
            ->tap(function (Collection $results) {
                $this->table(
                    ['Type', 'Result'],
                    $results->toArray()
                );
            });

        return self::SUCCESS;
    }
}
