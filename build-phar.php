<?php

  if (php_sapi_name() != 'cli') {
    die("Please use CLI to run this script");
  }

  $version = isset($argv[1]) && $argv[1] ? $argv[1] : null;

  if (empty($version)) {
    die("Version number is required\n");
  }

  if (isset($argv[2]) && $argv[2]) {
    $phar_path = rtrim($argv[1], '/') . "/narrative-{$version}.phar";
  } else {
    $phar_path = __DIR__ . "/dist/narrative-{$version}.phar";
  }

  if (is_file($phar_path)) {
    print "File '$phar_path' exists. Overwrite (y/n)?\n";

    if (strtolower(trim(fgets(STDIN))) === 'y') {
      unlink($phar_path);
    } else {
      die("Done, file kept...\n");
    }
  }

  require 'vendor/autoload.php';

  use Phine\Phar\Builder, Phine\Phar\Stub;

  $skip_if_found = [ '/.git', '/.svn', '/phpdocumentor', '/phpspec', '/phpunit', '/sebastian', '/smarty/documentation', '/smarty/development', '/tests', '/Tests' ];
  $source_path_strlen = strlen(__DIR__);

  $builder = Builder::create($phar_path);

  $builder->addFile(__DIR__ . '/LICENSE', 'LICENESE');

  foreach ([ 'bin', 'src', 'vendor' ] as $dir_to_add) {
    /**
     * @var RecursiveDirectoryIterator[] $iterator
     */
    foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/' . $dir_to_add, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
      $pathname = $item->getPathname();
      $short_pathname = substr($pathname, $source_path_strlen + 1);

      foreach ($skip_if_found as $what) {
        if (strpos($pathname, $what) !== false) {
          continue 2;
        }
      }

      if ($item->isDir()) {
        $builder->addEmptyDir($short_pathname);
      } elseif($item->isFile()) {
        $builder->addFile($pathname, $short_pathname);
      }

      print "Adding $short_pathname\n";
    }
  }

////  // ---------------------------------------------------
////  //  Add /src
////  // ---------------------------------------------------
////
////  foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/src', RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
////    $pathname = $item->getPathname();
////    $short_pathname = substr($pathname, $source_path_strlen + 1);
////
////    if ($item->isDir()) {
////      $builder->addEmptyDir($short_pathname);
////    } elseif($item->isFile()) {
////      $builder->addFile($pathname, $short_pathname);
////    }
////
////    print "Adding $short_pathname\n";
////  }
////
////  return;
////
////  // ---------------------------------------------------
////  //  Add /vendor
////  // ---------------------------------------------------
////
////
////
////  /**
////   * @var RecursiveDirectoryIterator[] $iterator
////   */
////  foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/vendor', RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
////    $pathname = $item->getPathname();
////
////    foreach ($skip_if_found as $what) {
////      if (strpos($pathname, $what) !== false) {
////        continue 2;
////      }
////    }
////
////    if ($item->isDir()) {
////      $builder->addEmptyDir(substr($pathname, $source_path_strlen + 1));
////    } elseif($item->isFile()) {
////      $builder->addFile($pathname, substr($pathname, $source_path_strlen + 1));
////    }
////
////    print 'Adding ' . substr($pathname, $source_path_strlen + 1) . "\n";
////  }
//
//  return;

//  $builder->buildFromIterator(
//    Finder::create()
//      ->files()
//      ->name('*.php')
//      ->exclude('Tests')
//      ->in(__DIR__ . "/vendor")
//  );

  $builder->setStub(
    Stub::create()
      ->mapPhar('narrative.phar')
      ->addRequire('bin/narrative')
      ->getStub()
  );

  die("\n" . basename($phar_path) . ' created. SHA1 checksum: ' . sha1_file($phar_path) . "\n");

