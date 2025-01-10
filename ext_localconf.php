<?php

defined('TYPO3') || die('Access denied.');

if(!class_exists(\Mpdf\Mpdf::class)){
    $composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('form_pdf')
        . 'Resources/Private/PHP/autoload.php';
    if(file_exists($composerAutoloadFile)) {
        require_once($composerAutoloadFile);
    }
}

// Add PDF cleanup task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Brightside\FormPdf\Task\CleanerTask::class] = [
    'extension' => 'form_pdf',
    'title' => 'LLL:EXT:form_pdf/Resources/Private/Language/locallang.xlf:form_pdf.tasks.cleaner.name',
    'description' => 'LLL:EXT:form_pdf/Resources/Private/Language/locallang.xlf:form_pdf.tasks.cleaner.description',
    'additionalFields' => \Brightside\FormPdf\Task\CleanerFieldProvider::class
];


$boot = function (): void {
    if (TYPO3 === 'BE') {
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            'pdf-finisher',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:form_pdf/Resources/Public/Icons/Extension.svg']
        );
    }

    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['pdfform'] = \Brightside\FormPdf\Ajax\PdfResponse::class . '::processRequest';
};
$boot();
unset($boot);
