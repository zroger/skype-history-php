<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('SkypeHistory')
             ->setChannel('zroger.github.com/pear')
             ->setSummary('A simple Skype call history viewer for OS/X.')
             ->setDescription('A simple Skype call history viewer for OS/X.')
             ->setReleaseVersion('1.0.0')
             ->setReleaseStability('stable')
             ->setApiVersion('1.0.0')
             ->setApiStability('stable')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('First stable release.')
             ->addMaintainer('lead', 'Roger López', 'zroger', 'code@zroger.com')
             ->addGitFiles()
             ->addExecutable('skype-history')
             ->addPackageDependency('Console_CommandLine', 'pear.php.net')
             ->addPackageDependency('Console_Table', 'pear.php.net')
             ;
