<?php
/**
 * @author MGriesbach@gmail.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link http://github.com/MSeven/cakephp_queue
 */

namespace Queue\Shell\Task;

use Cake\Console\ConsoleIo;

/**
 * A Simple QueueTask example.
 */
class QueueRetryExampleTask extends QueueTask implements AddInterface {

	/**
	 * Timeout for run, after which the Task is reassigned to a new worker.
	 *
	 * @var int
	 */
	public $timeout = 10;

	/**
	 * Number of times a failed instance of this task should be restarted before giving up.
	 *
	 * @var int
	 */
	public $retries = 4;

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * Constructs this Shell instance.
	 *
	 * @param \Cake\Console\ConsoleIo|null $io IO
	 */
	public function __construct(ConsoleIo $io = null) {
		parent::__construct($io);

		$this->file = TMP . 'task_retry.txt';
	}

	/**
	 * Example add functionality.
	 * Will create one example job in the queue, which later will be executed using run();
	 *
	 * To invoke from CLI execute:
	 * - bin/cake queue add RetryExample
	 *
	 * @return void
	 */
	public function add() {
		$this->out('CakePHP Queue RetryExample task.');
		$this->hr();
		$this->out('This is a very simple example of a QueueTask and how retries work.');
		$this->out('I will now add an example Job into the Queue.');
		$this->out('This job will only produce some console output on the worker that it runs on.');
		$this->out(' ');
		$this->out('To run a Worker use:');
		$this->out('    bin/cake queue runworker');
		$this->out(' ');
		$this->out('You can find the sourcecode of this task in: ');
		$this->out(__FILE__);
		$this->out(' ');

		if (file_exists($this->file)) {
			$this->warn('File seems to already exist. Make sure you run this task standalone. You cannot run it multiple times in parallel!');
		}

		file_put_contents($this->file, '0');

		$this->QueuedJobs->createJob('RetryExample');
		$this->success('OK, job created, now run the worker');
	}

	/**
	 * Example run function.
	 * This function is executed, when a worker is executing a task.
	 * The return parameter will determine, if the task will be marked completed, or be requeued.
	 *
	 * @param array $data The array passed to QueuedJobsTable::createJob()
	 * @param int $jobId The id of the QueuedJob entity
	 * @return void
	 * @throws \Exception
	 */
	public function run(array $data, $jobId) {
		$count = (int)file_get_contents($this->file);

		$this->hr();
		$this->out('CakePHP Queue RetryExample task.');
		$this->hr();

		// Let's fake 3 fails before it actually runs successfully
		if ($count < 3) {
			$count++;
			file_put_contents($this->file, (string)$count);
			$this->abort(' -> Sry, the RetryExample Job failed. Try again. <-');
		}

		unlink($this->file);
		$this->success(' -> Success, the RetryExample Job was run. <-');
	}

}
