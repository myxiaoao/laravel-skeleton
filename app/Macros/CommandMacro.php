<?php

namespace App\Macros;

use Throwable;

/**
 * @mixin \Illuminate\Console\Command
 *
 * @see https://github.com/nunomaduro/laravel-console-task
 */
class CommandMacro
{
    /**
     * ```php
     * $this->task('Successful task.', function (){
     *     return true;
     * });
     *
     * $this->task('Failed task.', function (){
     *     return false;
     * });
     *
     * // Specify a 3rd parameter for a custom loading message
     * // Default is `loading...`
     * $this->task('Long task.', function (){
     *     sleep(3);
     *
     *     return true;
     * }, 'sleeping...');
     * ```
     */
    public function task(): callable
    {
        /*
         * Performs the given task, outputs and
         * returns the result.
         *
         * @param  string $title
         * @param  callable|null $task
         *
         * @return bool With the result of the task.
         */
        return function (string $title, $task = null, $loadingText = 'loading...') {
            $this->output->write("$title: <comment>{$loadingText}</comment>");

            if ($task === null) {
                $result = true;
            } else {
                try {
                    $result = $task() === false ? false : true;
                } catch (Throwable $taskException) {
                    $result = false;
                }
            }

            if ($this->output->isDecorated()) { // Determines if we can use escape sequences
                // Move the cursor to the beginning of the line
                $this->output->write("\x0D");

                // Erase the line
                $this->output->write("\x1B[2K");
            } else {
                $this->output->writeln(''); // Make sure we first close the previous line
            }

            $this->output->writeln(
                "$title: ".($result ? '<info>✔</info>' : '<error>failed</error>')
            );

            if (isset($taskException)) {
                throw $taskException;
            }

            return $result;
        };
    }
}
