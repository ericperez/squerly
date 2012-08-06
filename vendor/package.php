<?php
error_reporting(0);
require_once 'PEAR/PackageFileManager2.php';
// recommended - makes PEAR_Errors act like exceptions (kind of)
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagexml = new PEAR_PackageFileManager2();
$packagexml->setOptions(
    array(
    'filelistgenerator' => 'file',
    'packagedirectory' => dirname(__FILE__),
    'baseinstalldir' => '/',
    'dir_roles' => array(
        'examples' => 'doc',
        'docs'     => 'doc',
        'tests'    => 'test'
        ),
    'ignore' => array(
        'package.php',
        'package.xml',
        'package.xml.old'
    ),
    'simpleoutput' => true)
);
$packagexml->setPackageType('php');
$packagexml->addRelease();
$packagexml->setPackage('Create_KML');
$packagexml->setChannel('pear.php.net');
$packagexml->setReleaseVersion('0.1.0');
$packagexml->setAPIVersion('0.1.0');
$packagexml->setReleaseStability('alpha');
$packagexml->setAPIStability('alpha');
$packagexml->setSummary('Class to create KML code from a set of data');
$packagexml->setDescription('Class for creating KML code from a data source and outputing it to either a file or string');
$packagexml->setNotes('Various fixes and changes as suggested from the comments on proposal.
* The function to create the KML code now uses the SimpleXML class.
* Function to create KML code is now in the magic __toString() method under the XML_KML_Create
* Removed the save() method
* Implemented constructers and destructers
* Implemented one-class-per-file rule adding Main.php KML/Place.php and KML/Style.php
* Changed class name KML to XML_KML_Create
* Changed class name KMLPlace to XML_KML_Place
* Changed class name KMLStyle to XML_KML_Style
* Implemented set*() methods on XML_KML_Style and XML_KML_Place that validate input
* Code now adheres to PHPCodeSniffer standards');
$packagexml->setPhpDep('5.3.0');
$packagexml->setPearinstallerDep('1.7.0');
$packagexml->addMaintainer('lead', 'hamstar', 'Robert McLeod', 'hamstar@telescum.co.nz');
$packagexml->setLicense(
    'LGPL License 2.1',
    'http://www.gnu.org/copyleft/lesser.html'
);
$packagexml->addGlobalReplacement('package-info', '@PEAR-VER@', 'version');
$packagexml->generateContents();

if (isset($_GET['make'])
    || (isset($_SERVER['argv'])
    && @$_SERVER['argv'][1] == 'make')
) {
    $packagexml->writePackageFile();
} else {
    $packagexml->debugPackageFile();
}
// vim:set et ts=4 sw=4:
?>
