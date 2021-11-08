<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

use Bitrix\Main\Config\Option;

use Bitrix\Main\Localization\Loc; 
Loc::loadMessages(__FILE__);

use Bitrix\Main\Application;
$request = Application::getInstance()->getContext()->getRequest();

use Bitrix\Main\Loader; 
if(!\Bitrix\Main\Loader::includeModule("sale") || !\Bitrix\Main\Loader::includeModule("catalog") || !\Bitrix\Main\Loader::includeModule("iblock") || !\Bitrix\Main\Loader::includeModule("aspro.next"))
{
  echo "failure";
  return;
}

$APPLICATION->SetTitle(Loc::getMessage("SETTINGS_TITLE"));?>

<?=BeginNote()?>
	<?=Loc::getMessage("HELP")?>
<?=EndNote()?>

<style type="text/css">
	.adm-detail-content-cell-r .adm-info-message {
		margin: 0 !important;
	}
</style>


<?if(!$_POST['field_11'] || !$_POST['field_49']) {
	echo "<p><b>Не заполнены коэффициенты!</b></p>";
}?>
<form method="post" enctype="multipart/form-data">


	<?php
	$aTabs = array(
		array(
			"DIV" => "instagram", 
			"TAB" => Loc::getMessage("TAB_BTN"), 
			"TITLE" => Loc::getMessage("TAB_TITLE")
		),
	);
	$settingsTabs = new CAdminTabControl("settingsTabs", $aTabs, true, true);
	$settingsTabs->Begin();
	?>
	<?php $settingsTabs->BeginNextTab(); ?>
    <input name="zup" type="hidden" value="Y1" />
        <tr>
            <td width="40%" class="adm-detail-content-cell-l">
                <b><?=Loc::getMessage("IBLOCK_FIELD_ID")?>:</b>
            </td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="number" name="ID_PRODCT" size="30" value="">
            </td>
        </tr>
		<tr>
			<td width="40%" class="adm-detail-content-cell-l">
				<b><?=Loc::getMessage("IBLOCK_FIELD_NAME")?>:</b>
			</td>
			<td width="60%" class="adm-detail-content-cell-r">
                <input type="number" step="0.01" name="field_11" size="30" value="<?=$field?>">
			</td>
		</tr>
		<tr>
			<td width="40%" class="adm-detail-content-cell-l">
                <b><?=Loc::getMessage("FIELD_NAME")?>:</b>
			</td>
			<td width="60%" class="adm-detail-content-cell-r">
				<input type="number" step="0.01" name="field_49" size="30" value="<?=$field?>">
			</td>
		</tr>
	<?php
	$settingsTabs->Buttons(array(
		"btnSave" => false,
		"btnApply" => true,
		"btnCancel" => false,
	));
	$settingsTabs->End();
	?>
</form>

<?

if($_POST['field_11'] || $_POST['field_49']) {

    if($_POST['ID_PRODCT']) {

        $products = CCatalogSKU::getOffersList(
            intVal($_POST['ID_PRODCT']), // массив ID товаров
            20, // указываете ID инфоблока только в том случае, когда ВЕСЬ массив товаров из одного инфоблока и он известен
            $skuFilter = array(), // дополнительный фильтр предложений. по умолчанию пуст.
            $fields = array(),  // массив полей предложений. даже если пуст - вернет ID и IBLOCK_ID
            $propertyFilter = array() /* свойства предложений. имеет 2 ключа:
                                   ID - массив ID свойств предложений
                                          либо
                                   CODE - массив символьных кодов свойств предложений
                                         если указаны оба ключа, приоритет имеет ID*/
        );

        $PRICE_TYPE_ID = 1;
        foreach($products as $offers){
            foreach ($offers as $offer) {
                $PRODUCT_ID = $offer['ID'];
                $first_price = CPrice::GetBasePrice($offer['ID'], 1, 10);
                $second_price = CPrice::GetBasePrice($offer['ID'], 11, 49);

                if($first_price["PRICE"]) {
                    $arFieldsOne = Array(
                        "PRODUCT_ID" => $PRODUCT_ID,
                        "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
                        "PRICE" => $first_price["PRICE"]*floatVal($_POST['field_11']),
                        "CURRENCY" => "USD"
                    );
                    CPrice::Update($first_price["ID"], $arFieldsOne);
                }

                if($second_price["PRICE"]) {
                    $arFieldsTwo = Array(
                        "PRODUCT_ID" => $PRODUCT_ID,
                        "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
                        "PRICE" => $second_price["PRICE"]*floatVal($_POST['field_49']),
                        "CURRENCY" => "USD"
                    );
                    CPrice::Update($second_price["ID"], $arFieldsTwo);
                }
            }
        }
    }else {
        $arSelect = Array("ID", "NAME");
        $arFilter = Array("IBLOCK_ID"=>23, "ACTIVE"=>"Y");
        $res = CIBlockElement::GetList(Array("ID" => "ASC"), $arFilter, false, false, $arSelect);
        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $products[] = $arFields["ID"];
        }

        $PRICE_TYPE_ID = 1;
        foreach($products as $offer){

            if($offer == '1082'){

                $PRODUCT_ID = $offer;

                $first_price = CPrice::GetBasePrice($offer, 1, 10);
                $second_price = CPrice::GetBasePrice($offer, 11, 49);

                if($first_price["PRICE"]){
                    $arFieldsOne = Array(
                        "PRODUCT_ID" => $PRODUCT_ID,
                        "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
                        "PRICE" => $first_price["PRICE"]*floatVal($_POST['field_11']),
                        "CURRENCY" => "USD"
                    );

                    CPrice::Update($first_price["ID"], $arFieldsOne);
                }

                if($second_price["PRICE"]){


                    $arFieldsTwo = Array(
                        "PRODUCT_ID" => $PRODUCT_ID,
                        "CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
                        "PRICE" => $second_price["PRICE"]*floatVal($_POST['field_49']),
                        "CURRENCY" => "USD"
                    );

                    CPrice::Update($second_price["ID"], $arFieldsTwo);
                }

            }
        }
    }
}?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>