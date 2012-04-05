<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('SkypeHistory')
             ->setChannel('zroger.github.com/pear')
             ->setSummary('A simple Skype call history viewer for OS/X.')
             ->setDescription('A simple Skype call history viewer for OS/X.')
             ->setReleaseVersion('0.0.1')
             ->setReleaseStability('alpha')
             ->setApiVersion('0.0.1')
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'Roger López', 'zroger', 'code@zroger.com')
             ->addGitFiles()
             ->addExecutable('skype-history')
             ->addPackageDependency('Console_CommandLine', 'pear.php.net')
             ->addPackageDependency('Console_Table', 'pear.php.net')
             ;
