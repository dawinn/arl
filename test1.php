<?
$is_show_variant = 0; // буфер для предварительной сборки блока вывода
$hidedivblock = ""; // вспомогательный скрытый блок формируется в процессе перебора торговых предложений
$hidedivsblock = ""; // вспомогательный скрытый блок формируется в процессе перебора торговых предложений нужен для поиска по артикулу
$PhotoLarge = ($tmpfile[3] ? "/netcat_files/".$tmpfile[3] : $f_PhotoLarge); // Если в торговом предложении не задано основное фото, берем из карточки.
$show_btn = "";
$vPic     = "";
$vItemID  = "";
$vColorName = "";

if ($db->get_var("SELECT COUNT(*) as n FROM Message$classID WHERE Parent_Message_ID=".$f_RowID." AND Checked=1") > 0){
  $is_show_variant = 1; // флаг наличия торговых предложений для товара

  /// выясняем торговое предложение с минимальной ценой
  ///чтобы отметить его как активное при загзуке страницы

  $row_minPrice = $db->get_row("SELECT Message_ID as id , groupe, Price, Price1
                                FROM Message$classID
                                WHERE Checked=1 AND Message_ID = ".$curcolor."
                                ORDER BY Price, Priority
                                limit 1",ARRAY_A,0);

  if (!isset($row_minPrice)) {
    $row_minPrice = $db->get_row("SELECT Message_ID as id, groupe, Price, Price1
                                  FROM Message$classID
                                  WHERE Parent_Message_ID=".$f_RowID." AND Checked=1
                                  ORDER BY Price, Priority
                                  limit 1",ARRAY_A,0);
  }

  /// получение торговых предложений товара
  $lres = mysql_query("SELECT * FROM Message$classID as a LEFT
                      JOIN Classificator_colorGroupe as b ON (a.groupe = b.colorGroupe_ID)
                      WHERE a.Parent_Message_ID=".$f_RowID." AND a.Checked=1
                      ORDER BY b.colorGroupe_Priority, a.Priority");

  /// первый элемент обрабатывается отдельно

  $selectGroupe = "";
  $lrow = mysql_fetch_assoc($lres);
  $tmpfile = explode(":", $lrow[PhotoSmall]);
  $fileSPic = ($tmpfile[3] ? "/netcat_files/".$tmpfile[3] : "/images/arluma/images/noimg.png");
  $tmpfile = explode(":", $lrow[PhotoLarge]);

  ///Если в торговом предложении не задано основное фото, берем из карточки.
  $PhotoLarge = ($tmpfile[3] ? "/netcat_files/".$tmpfile[3] : $f_PhotoLarge);
  $ItemID = $lrow[ItemID]; // Артикул
  $colorName = htmlspecialchars($lrow[colorName],ENT_QUOTES); // Название товара по артикулу

  $div_class = (($lrow['new'] && ($lrow['newe'] > date("Y-m-d H:i:s")))
                ?
                "class='new".($lrow[Message_ID] == $row_minPrice[id] ? " current" : "")."'"
                :
                ($lrow[Message_ID] == $row_minPrice[id] ? "class='current'" : ""));

  if ($is_show_price || is_belorus_region()) {
    $price = $factor*($lrow[Price] ? $lrow[Price] : $f_Price);
  }else{
    /* Любой регион кроме Москвы и белоруссии */
    $price = ($lrow[Price1] ? $lrow[Price1] : $f_Price1);
  }

  if ($lrow[groupe]){
    $groupeColor = $lrow[colorGroupe_Name];
    $groupeID = $lrow[colorGroupe_ID] ;
    /// открыли <div class='colors'>
    $varpic = "<div class='colors'>
                <span><p><b>$groupeColor</b></p></span>
                <div id='grbloc_".$groupeID."' class='grtab col".$groupeID."' ".($groupeID != $row_minPrice[groupe] ? "style=''" : "").">";

    $selectGroupe = "<select class='select_vol' required>
                      <option ".($groupeID == $row_minPrice[groupe] ? "selected='selected'" : "")." value='".$groupeID."'>".$groupeColor."</option>";
  }else{
        $varpic = "<div class='colors'>
                    <span><p><b>$groupeColor</b></p></span>
                    <div id='grbloc_' class='grtab col'>";
  }// end if

    // страховка на случай отсутствия группировок

  if ($lrow['Message_ID'] == $row_minPrice['id']) {
    $vPic 		= $PhotoLarge;
    $vItemID 	= $ItemID;
    $vColorName = $colorName;
    $vPrice 	= $price;
  }

  /* формирование торговых предложений */
  $varpic .= "<div id='item".$lrow[Message_ID]."' ".$div_class." href='".$PhotoLarge."' title='".$ItemID." - ".$colorName."'  price='".$price."' item='".$ItemID."' > <img src='".$fileSPic."' alt='".$colorName."' /> </div> ";
	$fullName = $lrow[Titl].($ItemID ? ", ".$ItemID : "").($colorName ? ", ".$colorName : "").($groupeColor ? ", ".$groupeColor : "");

  if ($nc_minishop->in_cart($fullName, $price)){
    $show_btn = "style='display:none;'";
  }
  $st = $nc_minishop->show_put_button($fullName, $price, $lrow[Message_ID], 1);

  /* формируем поля для кнопки торгового предложение */
  eval("\$input_val = $st");
  if (!$nc_minishop->in_cart($fullName, $price)) {
    $hidedivblock .= "<div id='bitem".$lrow[Message_ID]."' class='ms_cart_cont'>
                        <div id='nc_mscont_".$input_val[hash]."' class='nc_msput'>
                          <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][name]' value='".$input_val[name]."'>
                          <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][price]' value='".$input_val[price]."'>
                          <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][hash]' value='".$input_val[hash]."'>
                          <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][uri]' value='".$input_val[uri]."'>
                          <span class='minus' >-</span>
                          <input class='nc_msvalues nc_msquantity' type='text' name='good[".$input_val[id]."][quantity]' size='2'  value='1'>
                          <span class='plus' >+</span>
                        </div>
                      </div>";
  } else {
    $hidedivblock .= "<div id='bitem".$lrow[Message_ID]."' class='ms_cart_cont' >
                        <a class='already' href='#' style='cursor: default; top: 10px; background:url(/images/template1/i/images_gray-green/in_cart-2.png) no-repeat; width:37px; height:25px; display:block; outline:none; position:relative;' title='Этот товар уже в корзине'></a>
                      </div>";
  }//end if

  $hidedivsblock .= "<div class='item' >".$lrow[ItemID]." - ".$lrow[colorName]."</div>";
  $row_index = 2;

  while ($lrow = mysql_fetch_assoc($lres)){
    $tmpfile = explode(":", $lrow[PhotoSmall]);
    $fileSPic = ($tmpfile[3] ? "/netcat_files/".$tmpfile[3] : "/images/arluma/images/noimg.png");
    $tmpfile = explode(":", $lrow[PhotoLarge]);
    ///Если в торговом предложении не задано основное фото, берем из карточки.
    $PhotoLarge = ($tmpfile[3] ? "/netcat_files/".$tmpfile[3] : $f_PhotoLarge);
    $ItemID = $lrow[ItemID]; // Артикул
    $colorName = htmlspecialchars($lrow[colorName],ENT_QUOTES); // Название товара по артикулу

    if ($lrow[Message_ID] == $row_minPrice[id]) {
      $vPic = $PhotoLarge;
      $vItemID = $ItemID;
      $vColorName = $colorName;
      $vPrice = $price;
    }// end if

    $div_class = (($lrow['new'] && ($lrow['newe'] > date("Y-m-d H:i:s")))
                  ?
                  "class='new".($lrow[Message_ID] == $row_minPrice[id] ? " current" : "")."'"
                  :
                  ($lrow[Message_ID] == $row_minPrice[id] ? "class='current'" : "") );

    if ($groupeColor != $lrow[colorGroupe_Name] ) {
      $groupeColor = $lrow[colorGroupe_Name];
      $groupeID = $lrow[colorGroupe_ID] ;

      $varpic .= "</div>
                  <span><p><b>".$groupeColor."</b></p></span>
                  <div id='grbloc_".$groupeID."' class='grtab col".$groupeID."' ".($groupeID != $row_minPrice[groupe] ? "style='display:inline-block;'" : "").">";

      $selectGroupe .= "<option value='".$groupeID."' ".($groupeID == $row_minPrice[groupe] ? "selected='selected'" : "").">".$groupeColor."</option>";
    }// end if

    if ($is_show_price || is_belorus_region()) {
      $price = $factor*($lrow[Price] ? $lrow[Price] : $f_Price);
    }else{
      /* Любой регион кроме Москвы и белоруссии */
      $price = ($lrow[Price1] ? $lrow[Price1] : $f_Price1);
    }

    $varpic .= "<div id='item".$lrow[Message_ID]."' ".$div_class." href='".$PhotoLarge."' title='".$ItemID." - ".$colorName."'  price='".$price."' item='".$ItemID."' >
                  <img src='".$fileSPic."' alt='".$colorName."' />
                </div>";

    $fullName = $lrow[Titl].($ItemID ? ", ".$ItemID : "").($colorName ? ", ".$colorName : "").($groupeColor ? ", ".$groupeColor : "");

    $st = $nc_minishop->show_put_button($fullName, $price, $lrow[Message_ID], $row_index);
    eval("\$input_val = $st");

    if (!$nc_minishop->in_cart($fullName, $price)) {
      $hidedivblock .= "<div id='bitem".$lrow[Message_ID]."' class='ms_cart_cont' style='display:none;'>
                          <div id='nc_mscont_".$input_val[hash]."' class='nc_msput' >
                            <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][name]' value='".$input_val[name]."'>
                            <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][price]' value='".$input_val[price]."'>
                            <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][hash]' value='".$input_val[hash]."'>
                            <input class='nc_msvalues' type='hidden' name='good[".$input_val[id]."][uri]' value='".$input_val[uri]."'>
                            <span class='minus' >-</span>
                            <input class='nc_msvalues nc_msquantity' type='text' name='good[".$input_val[id]."][quantity]' size='2'  value='0'>
                            <span class='plus' >+</span>
                          </div>
                        </div>";
    } else {
      $hidedivblock .= "<div id='bitem".$lrow[Message_ID]."' class='ms_cart_cont' style='display:none;'>
                          <a class='already' href='#' style='cursor: default; top: 10px; background:url(/images/template1/i/images_gray-green/in_cart-2.png) no-repeat; width:37px; height:25px; display:block; outline:none; position:relative;' title='Этот товар уже в корзине'></a>
                        </div>";
    }//end if

    $hidedivsblock .=  "<div class='item'>".$lrow[ItemID]." - ".$lrow[colorName]."</div>";

    $row_index +=1;
  }//end while

  $varpic .= "</div>"; // закрыт <div class="grtab"

  $selectGroupe = "";// ($selectGroupe ? "<p><strong> Выбрать объем: </strong>".$selectGroupe."</select></p>" : "");
  $varpic .= "</div>
              <div class='clear20'></div>".$selectGroupe."";
}else{
  /* торговых предложений нет определяем основное изображение для карточки товара */
  $PhotoLarge = $f_PhotoLarge;
}// end if

/**  Вывод карточки на сайт  **/
?>
<div itemscope itemtype="http://schema.org/Product">
  <div class="product b-cl-506-rtf">
    <div id="sticky" class="preview">
      <a class="logo_brand" href="#" title=""><?=opt($current_sub[bannerLogo], "<img src='".$current_sub[bannerLogo]."' width='120' height='120' alt='' />")?></a>
      <div class="cover"><img itemprop="image" src="<?= ($vPic ? $vPic : $PhotoLarge) ?>" width="276" height="276" alt="<?= ($vItemID ? $vItemID : $ItemID) ?> - <?= ($vColorName ? $vColorName : $colorName) ?>" /></div>
      <div class="art">артикул:
        <em><?= ($vItemID ? $vItemID : $ItemID) ?> - <?= ($vColorName ? $vColorName : $colorName) ?></em>
      </div>

      <? if ($price) { ?>
      <div class="meta">
        <div class="recommended-price">
          <span class="title">Рекомендованная розничная цена</span>
          <div class="price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <span itemprop="price"><?=($vPrice ? $vPrice : $price); ?></span> руб.
            <meta name="priceCurrency" content="RUB" itemprop="priceCurrency"/>
          </div>

          <?=$nc_minishop->mass_put_header()?>
          <?=$hidedivblock;?>
          <div onclick="yaCounter20816275.reachGoal('ORDER'); return true;">
            <input class="seobuy buybtn" <?=$show_btn;?> onclick="nc_minishop_send_form(this.form.id,this.form.action);return false;" type="submit" value="Купить" >
          </div>
        </div>
      </div>
      <?} else {?>
      <div style="display: none;" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        <span itemprop="price">0</span>
        <meta name="priceCurrency" content="RUB" itemprop="priceCurrency"/>
      </div>
      <? } ?>

    </div>

    <div class="entry">
      <h1 itemprop="name"><?= $f_Titl ?></h1>
      <?= opt( $f_AngTitl, "<h2>".$f_AngTitl."</h2>")?>

      <?= ($f_PossAppFull
          ?
          "<div><b>ВОЗМОЖНОЕ ПРИМЕНЕНИЕ:</b> прекрасное решение для ".$f_PossAppFull."</div>"
          :
          opt( $f_PossApp, "<p><b>ВОЗМОЖНОЕ ПРИМЕНЕНИЕ:</b> прекрасное решение для ".$f_PossApp."</p>")
          )?>

      <? if ($is_show_variant == 1) {?>
        <h5>Цветовая палитра</h5>
        <p>Для уточнения цены кликните на интересующий вариант</p>
        <?=$varpic;?>
      <?}?>

      <? if ($is_show_price || is_belorus_region()) {
        $price = $factor*($row_minPrice[Price] ? $row_minPrice[Price] : $f_Price);
      }else{
        /* Любой регион кроме Москвы и белоруссии */
        $price = ($row_minPrice[Price1] ? $row_minPrice[Price1] : $f_Price1);
      }?>

    </div>
  </div>
</div>
<?= $hidedivsblock;?>
<div class="clear20"></div>
<!-- возможно проблема тут-->
<div class="selection" style="float:inherit; width:100%;">
  <ul class="tabs">
    <li class="t1 current">Описание</li>
    <?= (($f_TechFile || $f_InstructionsFile || $f_consumptionOfPaint) ? "<li class='t2'>Тех.документация</li>" : "")?>
    <?= ($f_certificateFile ? "<li class='t3'>Сертификаты</li>" : "")?>
    <?= ($f_videos ? "<li class='t4'>Видео</li>" : "")?>
    <?= ($f_inspiration ? "<li class='t5'>Вдохновение</li>" : "")?>
    <li class='t7'>Купить в рознице</li>
    <?/*<li class="t6  retail"  >
          <a target="blank_" href='http://arluma.ru/buy/'>Купить в рознице</a></li>
      */?>
  </ul>

  <div class="tabs-cont-list">
    <div class="t1" itemprop="description">
      <?= opt( $f_TextFull, $f_TextFull)?>
      <?= opt( $f_seotext, $f_seotext)?>
    </div>

    <? if ($f_TechFile || $f_InstructionsFile || $f_consumptionOfPaint) {?>
      <div class="t2">
        <div class="meta">
          <?= opt($f_InstructionsFile,"<a class='docs' href='".$f_InstructionsFile."' title='' target='_blank'>Инструкция по нанесению (скачать)</a>")?>
        </div>

        <? if ($f_TechFile) { ?>
          <div class="dl-doc-link">
            <a class='docs' href='<?=$f_TechFile;?>' target='_blank' download>Скачать файл</a>
          </div>
          <iframe class="viewer-pdf" src="https://docs.google.com/viewer?url=http://cat.arluma.ru<?=$f_TechFile?>&embedded=true"
style="width: 700px; height: 600px;" frameborder="0">Ваш браузер не поддерживает фреймы</iframe>
        <? }?>

        <table class="tab-param">
          <tbody>
            <? if (!empty($f_consumptionOfPaint)) {?>
            <tr>
              <td> расход:</td>
              <td><?=$f_consumptionOfPaint;?></td>
            <tr>
              <?}?>
          </tbody>
        </table>
      </div>
    <?}?>

    <? if ($f_certificateFile) {?>
      <div class="t3">
        <div class="dl-doc-link">
          <a class='docs' href='<?=$f_certificateFile;?>' target='_blank' download>Скачать файл</a>
        </div>
        <iframe class="viewer-pdf" src="https://docs.google.com/viewer?url=http://cat.arluma.ru<?=$f_certificateFile?>&embedded=true"
style="width: 700px; height: 600px;" frameborder="0">Ваш браузер не поддерживает фреймы</iframe>
      </div>
    <?} /*end if $f_certificateFile*/?>

    <? if($f_videos){
      $arr_video_id = explode(',',substr($f_videos,1,strlen($f_videos)-2));
      $sqlwhere = "(0";
      ?>

      <div class='t4'>

        <? foreach ($arr_video_id as $videolist){
          $sqlwhere .= " OR Message_ID = ".$videolist;
        }

        $sqlwhere .= ")";
        ?>

        <div class='similar_products adtext'>
          <?= listQuery("SELECT Name, Frame, VideoLink  FROM Message512 WHERE ".$sqlwhere." AND checked",
                      "<p><strong>\$data[Name]</strong></p>
                      <div class='clear'></div>\$data[Frame] <div class='clear20'></div>") ?>

          <p style='padding-left: 211px; float:none; '>
            <a href='http://arluma.ru/help/video/'>Посмотреть все видеоролики</a>
          </p>
        </div>
      </div>
    <?} /*end if f_videos*/?>

    <? if($f_inspiration){
      $arr_inspiration_id = explode(',',substr($f_inspiration,1,strlen($f_inspiration)-2));
      $sqlwhere = "(0";
      ?>
      <div class='t5'>
        <?
        foreach ($arr_inspiration_id as $inspirationlist) {
          $sqlwhere .= " OR Subdivision_ID = ".$inspirationlist;
        }
        $sqlwhere .= ")";
        ?>
        <h5>Интересные решения / мастер класс</h5>
        <p>&nbsp;</p>

        <?
        $lres = mysql_query("SELECT imgBigInspiration as filesrc, Hidden_URL as url, Subdivision_Name as name  FROM Subdivision  WHERE ".$sqlwhere." AND checked");

        while ($lrow = mysql_fetch_assoc($lres)) {
          $filesrc = explode(":", $lrow[filesrc]);
          echo "<a href='http://arluma.ru".$lrow[url]."' title='".$lrow[name]."'> <img src='/netcat_files/".$filesrc[3]."' width='600' alt='".$lrow[name]."' style='margin-left: 100px;' /></a>";
        }// end while
        ?>
      </div>
    <?}/*end if $f_inspiration*/?>

    <div class="t7">
      <?= nc_objects_list(505, 661); ?>
    </div>
  </div>
</div>
<div class="clear20"></div>
<div class="clear20"></div>


<? if ($f_Tags) {?>
  <?
  $sqlwhere = "(0";
  foreach ($f_Tags_id as $itemTag){
    $sqlwhere .= " OR a.Tags LIKE '%,".$itemTag.",%'";
  }
  $sqlwhere .= ")";
  ?>

<div class='similar_products'>
	<h5>Вас также может заинтересовать:</h5>
    <ul>
	<?
         $lres = mysql_query("SELECT a.*,b.* FROM Message$classID as a left join Subdivision as b on (a.Subdivision_ID = b.Subdivision_ID) WHERE a.Parent_Message_ID = 0 AND a.Checked=1 AND a.Message_ID != $message AND $sqlwhere ORDER BY RAND() LIMIT 4");
         while ($lrow = mysql_fetch_assoc($lres)) {
		$fileLPic = explode(":", $lrow[PhotoLarge]);
                echo "<li>
                      <a href='".$lrow[Hidden_URL].$lrow[EnglishName]."_".$lrow[Message_ID].".html'><span class='cover'>
                         <img src='/netcat_files/".$fileLPic[3]."' width='200' height='200' alt='".$lrow[Titl]."' /></span><span>".$lrow[Titl]."</span></a>
                         <span class='price'>".opt_case($lrow[Price] && ($is_show_price || is_belorus_region()), "Цена*: ".opt($lrow[prefix_price],"от ").$factor*$lrow[Price]." руб.",
                            opt($lrow[Price1], "Цена*: ".opt($lrow[prefix_price],"от ").$lrow[Price1]." руб."))."</span></li>";
        }?>
    </ul>
</div>
<div class='clear8'></div>

    <?= ($is_show_price ?  "<div class='pricetext'>* - указана рекомендованная розничная цена, которая может варьироваться, в зависимости от ценовой политики розничного магазина</div>" :
   "<div class='pricetext'>* - указана рекомендованная розничная цена, уточняйте цены у наших партнёров в регионах</div>") ?>


<?}?>
