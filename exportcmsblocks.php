<?php

require_once('../app/Mage.php');
Mage::app('admin');

Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::register("isSecureArea", true);

$coreResource = Mage::getSingleton('core/resource');
$connect = $coreResource->getConnection('core_write');

$csv=array();
/* Identifiers of cms block/pages what we should export */
$keepcmspages=array('page1');
$keepcsmblocks=array('block1','block2');

/* CMS blocks */
$cmsblocks=Mage::getModel("cms/block")->getCollection()->addFieldToFilter("identifier", array('in'=> $keepcsmblocks));
foreach ($cmsblocks as $block) {
    $blockdata=array();
    $blockdata['id']=$block->getId();
    $blockdata['title']=$block->getTitle();
    $blockdata['identifier']=$block->getIdentifier();
    $blockdata['content']=$block->getContent();
    $csv[]=$blockdata;

}
$cmsBlock_file_path = 'cmsblock.csv';

try {
    saveData($cmsBlock_file_path, $csv);
} catch (Exception $e) {
    echo "[ERROR] Creating backup CMS Block file: " . $e->getMessage() . PHP_EOL;

}

echo "\033[42mStaticBlocks\033[0m exported - see " . $cmsBlock_file_path . PHP_EOL;

/* CMS pages */


$csv=array();

$cmspages=Mage::getModel("cms/page")->getCollection()->addFieldToFilter("identifier", array('in'=> $keepcmspages));
foreach ($cmspages as $page) {
    $blockdata=array();
    $blockdata['id']=$page->getPageId();
    $blockdata['title']=$page->getTitle();
    $blockdata['identifier']=$page->getIdentifier();
    $blockdata['content']=$page->getContent();
    $csv[]=$blockdata;

}
$cmsPage_file_path = 'cmspage.csv';

try {
    saveData($cmsPage_file_path, $csv);
} catch (Exception $e) {
    echo "[ERROR] Creating backup CMS Page file: " . $e->getMessage() . PHP_EOL;

}
echo "\033[41mPages\033[0m exported - see " . $cmsPage_file_path . PHP_EOL;


function saveData($file, $data) {
    $fh = fopen($file, 'w');
    foreach ($data as $dataRow) {
        fputcsva($fh, $dataRow);
    }
    fclose($fh);
    return $this;
}

function fputcsva(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"') {
    $str = '';
    $escape_char = '\\';
    foreach ($fields as $value) {
        if (strpos($value, $delimiter) !== false ||
            strpos($value, $enclosure) !== false ||
            strpos($value, "\n") !== false ||
            strpos($value, "\r") !== false ||
            strpos($value, "\t") !== false ||
            strpos($value, ' ') !== false) {
            $str2 = $enclosure;
            $escaped = 0;
            $len = strlen($value);
            for ($i = 0; $i < $len; $i++) {
                if ($value[$i] == $escape_char) {
                    $escaped = 1;
                } else if (!$escaped && $value[$i] == $enclosure) {
                    $str2 .= $enclosure;
                } else {
                    $escaped = 0;
                }
                $str2 .= $value[$i];
            }
            $str2 .= $enclosure;
            $str .= $str2 . $delimiter;
        } else {
            if (strlen($value)):
                $str .= $enclosure . $value . $enclosure . $delimiter;
            else:
                $str .= $value . $delimiter;
            endif;
        }
    }
    $str = substr($str, 0, -1);
    $str .= "\n";
    return fwrite($handle, $str);
}
