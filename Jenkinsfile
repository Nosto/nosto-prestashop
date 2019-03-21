#!/usr/bin/env groovy

pipeline {

  agent { dockerfile true }

  stages {
    stage('Prepare environment') {
      steps {
        checkout scm
      }
    }

    stage('Update Dependencies') {
      steps {
        sh "composer install --no-progress --no-suggest"
        sh "composer dump-autoload --optimize"
      }
    }

    stage('Code Sniffer') {
      steps {
        catchError {
          sh "./libs/bin/phpcs --standard=ruleset.xml --severity=10 --report=checkstyle --report-file=chkphpcs.xml . || true"
        }
        archiveArtifacts 'chkphpcs.xml'
      }
    }

    stage('Copy-Paste Detection') {
      steps {
        catchError {
          sh "./libs/bin/phpcpd --exclude=libs --exclude=build --log-pmd=phdpcpd.xml ."
        }
        archiveArtifacts 'phdpcpd.xml'
      }
    }

    stage('Mess Detection') {
      steps {
        catchError {
          sh "./libs/bin/phpmd . xml codesize,naming,unusedcode,controversial,design --exclude libs,var,build,tests --reportfile pmdphpmd.xml || true"
        }
        archiveArtifacts 'pmdphpmd.xml'
      }
    }

    stage('Phan Analysis') {
      steps {
        catchError {
          sh "./libs/bin/phan --config-file=phan.php --output-mode=checkstyle --output=chkphan.xml || true"
        }
        archiveArtifacts 'chkphan.xml'
      }
    }

    stage('Package') {
      steps {
        script {
          version = sh(returnStdout: true, script: 'grep "const PLUGIN_VERSION = " nostotagging.php | cut -d= -f2 | tr "," " "| tr ";" " " | tr "\'" " "').trim()
          sh "./vendor/bin/phing -Dversion=${version}"
        }
        archiveArtifacts "build/package/NostoTagging-${version}.zip"
      }
    }
  }

  post {
    always {
      checkstyle pattern: 'chk*.xml', unstableTotalAll:'0'
      pmd pattern: 'pmd*.xml', unstableTotalAll:'0'
      deleteDir()
    }
  }
}
