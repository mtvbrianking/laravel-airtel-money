<?php

require __DIR__.'/vendor/autoload.php';

use Doctum\Doctum;
use Doctum\RemoteRepository\GitHubRemoteRepository;
use Doctum\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = __DIR__.'/src';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('tests')
    ->exclude('vendor')
    ->in($dir);

$versions = GitVersionCollection::create($dir)
    // ->add('1.0', 'v1.0')
    // ->add('1.x', 'v1.x')
    ->add('main', 'main');

$repo = new GitHubRemoteRepository(
    'mtvbrianking/laravel-airtel-money',
    dirname($dir),
    'https://github.com/'
);

$options = [
    'theme' => 'default',
    'title' => 'Laravel Airtel Money',
    'versions' => $versions,
    'build_dir' => __DIR__.'/docs/%version%',
    'cache_dir' => __DIR__.'/docs/cache/%version%',
    'remote_repository' => $repo,
    'default_opened_level' => 2,
];

return new Doctum($iterator, $options);
