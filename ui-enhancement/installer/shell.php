<?php

if( ! isset( $_SERVER['argc'] ) )
	die( "Usage: php installer/shell.php\n" );
if( ! file_exists( 'db/local.php' ) )
	die( "Tiki is not installed yet.\n" );

if( isset( $_SERVER['argv'][1] ) && $_SERVER['argv'][1] != 'install' ) {
	$multi = basename( $_SERVER['argv'][1] );
}

require_once('lib/init/initlib.php');
require_once('lib/setup/tikisetup.class.php');
require_once('tiki-setup_base.php');
require_once('installer/installlib.php');
include $local_php;

echo "Running installer for: $local_php\n";

$installer = new Installer;
if( $_SERVER['argc'] == 2 && $_SERVER['argv'][1] == 'install' )
	$installer->cleanInstall();
else {
	$installer->update();

	if( count( $installer->installed ) ) {
		echo "\tPatches installed:\n";
		foreach( $installer->installed as $patch )
			echo "\t\t$patch\n";
	}

	if( count( $installer->executed ) ) {
		echo "\tScripts executed:\n";
		foreach( $installer->executed as $script )
			echo "\t\t$script\n";
	}
	
	echo "\tQueries executed successfully: " . count($installer->success) . "\n";
	if( count( $installer->failures ) ) {
		echo "\tErrors:\n";
		foreach( $installer->failures as $key => $error ) {
			list( $query, $message ) = $error;

			echo "\t===== Error $key =====\n\t$query\n\t$message\n";
		}
	}
}