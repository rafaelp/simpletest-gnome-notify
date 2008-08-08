#!/usr/bin/php
<?php

/*
simpletest-gnome-notify v0.1.1
Rafael Lima (http://rafael.adm.br) at Myfreecomm (http://myfreecomm.com.br)
http://rafael.adm.br/simpletest-gnome-notify
License: http://creativecommons.org/licenses/by/2.5/

Dependencies:

* Simpletest
  http://www.lastcraft.com/simple_test.php

* libnotify
  sudo apt-get install libnotify-bin

* pyinotify (for AutotestDaemon)
  sudo apt-get install python-pyinotify

*/

class SimpletestGnomeNotify {

  private $expiration_in_secs = 2;
  private $fail_image    = "gtk-dialog-error";
  private $pending_image = "gtk-dialog-warning";
  private $success_image = "gtk-dialog-info";

  public function run($path = '.') {
    chdir($path);

    /* Before running tests here I had a file which did this job
    $command = './tests/runAllTests.php';
    exec($command, $return, $status);
    */

    try {
      ob_start();
      $test = &new GroupTest('All tests');
      $files = scandir($path.'/tests');
      foreach($files as $file) {
        if(preg_match('/.test.php$/', $file)) {
          $test->addTestFile($path.'/tests/'.$file);
        }
      }
      $test->run(new TextReporter());
      $output = ob_get_contents();
      ob_end_clean();
    }
    catch(Exception $e) {
      $this->expiration_in_secs = 5;
      $this->notify("Error running test", $return[1], $this->fail_image);
      exit;
    }

    echo $output;
    $lines = split("\n",$output);
      
    foreach($lines as $line) {
      if(preg_match('/Test cases run: ([0-9]+)\/([0-9]+)/', $line, $matches)) {
        $testcases = $matches[1];
        preg_match('/Passes: ([0-9]+)/', $line, $matches);
        $passes = $matches[1];
        preg_match('/Failures: ([0-9]+)/', $line, $matches);
        $failures = $matches[1];
        preg_match('/Exceptions: ([0-9]+)/', $line, $matches);
        $exceptions = $matches[1];

        $examples = $passes+$failures+$exceptions;
      }
    }

    if($failures > 0) {
      $this->notify("Tests Failed", $failures."/".$examples.(($failures == 1) ? " test failed" :  " tests failed"), $this->fail_image);
    }
    elseif($exceptions > 0) {
      $this->notify("Tests with Exceptions", $exceptions."/".$examples.(($pendings == 1) ? " test is raising exceptions" : " tests are raising exceptions"), $this->pending_image);
    }
    elseif($pendings > 0) {
      $this->notify("Tests Pending", $pendings."/".$examples.(($pendings == 1) ? " test is pending" : " tests are pending"), $this->pending_image);
    }
    else {
      $this->notify("Tests Passed", "All ".$examples." tests passed", $this->success_image);
    }
  }
  
  private function notify($title, $message, $stock_icon) {
    $options = "-t ".($this->expiration_in_secs*1000)." -i ".$stock_icon;
    shell_exec("notify-send ".$options." '".$title."' '".$message."'");
  }

}

$simpletest_path = $argv[1];
if(empty($simpletest_path)) {
  echo "use ".__FILE__." [simpletest_path]\n";
  exit;
}
$simpletest_path = realpath($simpletest_path);
require_once($simpletest_path.'/unit_tester.php');
require_once($simpletest_path.'/reporter.php');

$path = realpath($argv[2]);
if(!file_exists($path.'/tests')) {
  echo "Path does seems to be corrected, if must have a directory called tests inside.\n";
  exit;
}

$test = new SimpletestGnomeNotify();
$test->run($path);

?>
