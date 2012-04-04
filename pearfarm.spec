<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('skype-history')
             ->setChannel('zroger.github.com/pear')
             ->setSummary('TODO: One-line summary of your PEAR package')
             ->setDescription('TODO: Longer description of your PEAR package')
             ->setReleaseVersion('0.0.1')
             ->setReleaseStability('alpha')
             ->setApiVersion('0.0.1')
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'TODO: Your name here', 'TODO: Your username here', 'TODO: Your email here')
             ->addGitFiles()
             ->addExecutable('skype-history')
             ;