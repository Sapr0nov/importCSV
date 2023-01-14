<?php
/*
Plugin Name: BeZ_importCSV
Description: Загрузка файла csv для графиков v 2.5
Version:  2.5 (25.07.2018)
Author: Sapronov Sergey
*/

add_action('admin_menu', 'bz_add_pages');

$path = $_SERVER['DOCUMENT_ROOT'];
 // подключаем
include_once $path . '/wp-config.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';
global $wpdb;  // теперь переменная $wpdb доступна

// Создается меню в главной консоли
function bz_add_pages() {
	// Добавляем пункт в основном меню
    add_menu_page('Загрузка данных для графиков', 'Загрузка Excel', 8, __FILE__, 'bz_toplevel_page', $path."/wp-content/plugins/bez_importCSV/icon.png", 76);
    add_submenu_page(__FILE__, 'Быстрое создание графиков', 'Быстрое создание графиков', 8, 'sub-page-chart-create', 'bz_sublevel_page');
    add_submenu_page(__FILE__, 'Редактирование графиков', 'Редактрирование графиков', 8, 'sub-page-chart-edit', 'bz_sublevel_page2');
    add_submenu_page(__FILE__, 'Создание нескольких графиков', 'Создание нескольких графиков', 8, 'sub-page-chart-create3', 'bz_sublevel_page3');
	add_submenu_page(__FILE__, 'Загрузка облигаций', 'Загрузка облигаций', 8, 'sub-page2', 'bz_sublevel_page4');
}

function bz_toplevel_page() {
    echo "<h2>Загрузка данных для графиков 2.5</h2>";
	bez_load_csv2();
}

function bz_sublevel_page() {
    echo "<h2>Быстрое создание графиков 2.5</h2>";
	bez_create_chart2();
}

function bz_sublevel_page2() {
    echo "<h2>Редактирование графиков 2.5</h2>";
	bez_edit_chart2();
}

function bz_sublevel_page3() {
    echo "<h2>Создание нескольких графиков</h2>";
	bez_create_chart3();
}

function bz_sublevel_page4() {
    echo "<h2>Загрузка и создание графиков Облигаций 2.0</h2>";
	bez_load_ob2();
}

// Загрузка данных из excel
function bez_load_csv2() {
	$k = 9;	// количество лет

// Создаётся окошко выбора года и какие компании обновлять
Echo "
      <h2><p><b> Форма для загрузки файлов </b></p></h2>
      <form action=\"\" method=\"post\" enctype=\"multipart/form-data\">
      <input type=\"file\" name=\"filename\"><br>
	  <label><input type=\"checkbox\" name=\"rus_company\"/> Обновить таблицу для русских компаний</label><br/>
	  <label><input type=\"checkbox\" name=\"ex_company\"/> Обновить таблицу для иностранных компаний</label><br/>
	  <label><input type=\"checkbox\" name=\"eur_company\"/> Обновить таблицу для европейских компаний</label><br/>
	  <label for=\"year\">Год с <label>
	  <select name=\"year\">
	  <option value=\"2005\">2005 по 2014</option>
	  <option value=\"2006\">2006 по 2015</option>
	  <option value=\"2007\">2007 по 2016</option>
	  <option value=\"2008\">2008 по 2017</option>
	  <option value=\"2009\" selected=\"selected\">2009 по 2018</option>
	  <option value=\"2010\">2010 по 2019</option>
	  <option value=\"2011\">2011 по 2020</option>
	  <option value=\"2012\">2012 по 2021</option>
	  <option value=\"2013\">2013 по 2022</option>
	  <option value=\"2014\">2014 по 2023</option>
	  <option value=\"2015\">2015 по 2024</option>
	  <option value=\"2016\">2016 по 2025</option>
	  <option value=\"2017\">2017 по 2026</option>
	  </select>
      <input type=\"submit\" value=\"Загрузить\"><br>
      </form>
</body>
</html>";

	// Проверка размера загруженного файла
	if(@$_FILES["filename"]["size"] > 1024*4*1024) {
		echo ("Размер файла превышает три мегабайта");
		exit;
	}
	// Проверка что файл фармата Excel
    if($_FILES["filename"]["type"] != "application/vnd.ms-excel") {
		echo "Формат файла должен быть Excel csv, разделенный запятыми.";
		exit;
	}
	// Проверяем загружен ли файл
	if(is_uploaded_file($_FILES["filename"]["tmp_name"])) {
		@mkdir(ABSPATH."uploads", 0777);
		$file = ABSPATH."uploads/".$_FILES["filename"]["name"];
		move_uploaded_file($_FILES["filename"]["tmp_name"], $file);
		echo "файл загружен<br/>";
	// Проверка установлен ли начальный год, если нет, то берётся 2009
	if (isset($_POST['year'])) {
		$year=(int)$_POST['year'];
	} else {
		$year = 2009;
	}
	echo "Год отсчета установлен - ".$year."<br/>";

	// начинаем с первого столбца
	$row = 0;
	// Открываем загруженный файл
	$handle = fopen($file, "r");
			print ($file);
			while (($data = fgetcsv($handle, 9000, ";")) !== FALSE) {
				if ($data[0]=='') {
					break;
				}
			$row++;
				// Получение и запись данных из загруженной таблицы (первые столбцы с общей информацией)
				$firma[$row] =		iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[0])));	// Тикер компании
				$security[$row] =	iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[1])));	// Название компании
				$link[$row] =		iconv("CP1251", "UTF-8", $data[2]);												// Ссылка на страницу компании
				$price[$row] =		iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[3])));	// Цена акции
				$akcii[$row] =		iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[4])));	// Кол-во акций
				$price_pref[$row] =	iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[5])));	// Цена привелигированной акции
				$akcii_pref[$row] =	iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[6])));	// Кол-во привелигированных акций
				$koeficient[$row] =	iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[7])));	// Коэффициент уменьшения порядка чисел
				$tikerr[$row] =		iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[89])));	// Только тикер компании
				$industry[$row] =	iconv("CP1251", "UTF-8", str_replace(',',' ',str_replace(' ',' ',$data[90])));	// Отрасль компании

				// Вычисление капитализации (Цена привелигированной акции * Кол-во привелигированных акций + Цена акции * Кол-во акций)
				$capitalizacia[$row] = $price_pref[$row]*$akcii_pref[$row] + $akcii[$row]*$price[$row];

				// Получение и запись данных из загруженной таблицы (столбцы с годовыми данными) (9 последних лет) (начиная с 8 столбца)
				for ($i = 8; $i <= ($k)*8; $i=$i+8) {
					$capital[$row][] =		floatval(iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i]))));		// Капитал
					$obligation[$row][] =			 iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+1])));	// Закредитованность
					$revenue[$row][] =				 iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+2])));	// Выручка
					$operProfit[$row][] =			 iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+3])));	// Операционная прибыль
					$freeProfit[$row][] =			 iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+4])));	// Чистая прибыль
					$CFO[$row][] =					 iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+5])));	// Денежный поток
					$Dividend[$row][] =				-iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+7])));	// Дивиденды
					$akcii_num[$row][] =			 iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+6])));	// Кол-во акций
				}
				// Получение и запись данных из загруженной таблицы (столбцы с годовыми данными рентабельности)
				for ($i = 0; $i < $k-2; $i=$i+1) {
					$buyback[$row][] = 		iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i+80])));			// Обратный выкуп
				}
			}

			// Закрываем загруженный файл
			@fclose($file);
			@unlink($file);

			// Создаём общую таблицу с компаниями
			$main_tbl[0] = array("Название","Тикер","Отрасль","Цена","E/P, %","ДП/P, %","Темп роста выручки за 5 лет, %","Выручка 17/16","Темп роста  ДП за 5 лет, %","ROE, %","Задол-ть","Див. Доходность","Дата отчета");

			for ($f=2; $f<=$row; $f++)	// первая строка - заголовки
			{
				// Таблица для стоимости компании
				$csv_tbl = $firma[$f].",".$security[$f].",".$price[$f].",".$akcii[$f].",".$price_pref[$f].",".$akcii_pref[$f].",".$koeficient[$f].", \r\n";

				// проверка данных последнего года для общей таблицы (если данные?)
				$Iyear = 0;
				if ((float)$freeProfit[$f][$Iyear]<>0 && (float)$capital[$f][$Iyear]<>0 && (float)$obligation[$f][$Iyear]<>0 && (float)$revenue[$f][$Iyear]<>0)
				{
					$Iyear = 0;
				}
				else
				{
					$Iyear = 1;
				}

				// Формат цены акции
				$price_a = (float)$price[$f];
				if ($price_a > 1000)
				{
					$price_a  = number_format($price_a, 1, '.', "'");
				}
				// Дата последнего отчёта
				$data = $year + 8 - $Iyear;
//--------------------------------------------------------------------------------------
				// Расчёт P/E (Цена акции * Кол-во акций + Цена привелигированной акции * Кол-во привелигированных акций)/(Чистая прибыль * коэффициент)
				if (((float)$freeProfit[$f][$Iyear]*(float)$koeficient[$f]) > 0)
				{
					$pe = number_format ( ((float)$price[$f] * (float)$akcii[$f] + (float)$price_pref[$f] * (float)$akcii_pref[$f]) / ((float)$freeProfit[$f][$Iyear]*(float)$koeficient[$f]) ,2);
				}
				else
				{
					$pe = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт P/ДП (Цена акции * Кол-во акций + Цена привелигированной акции * Кол-во привелигированных акций)/(Денежная прибыль * коэффициент)
				if (((float)$CFO[$f][$Iyear]) > 0)
				{
					$pdp = number_format ( ((float)$price[$f] * (float)$akcii[$f] + (float)$price_pref[$f] * (float)$akcii_pref[$f]) / ((float)$CFO[$f][$Iyear]*(float)$koeficient[$f]) ,2);
				}
				else
				{
					$pdp = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт E/P (Цена акции * Кол-во акций + Цена привелигированной акции * Кол-во привелигированных акций)/(Чистая прибыль * коэффициент)
				if (((float)$freeProfit[$f][$Iyear]*(float)$koeficient[$f]) > 0)
				{
					@$ep = number_format ( ((float)$freeProfit[$f][$Iyear]*(float)$koeficient[$f])*100 / ((float)$price[$f] * (float)$akcii[$f] + (float)$price_pref[$f] * (float)$akcii_pref[$f]) ,2);
				}
				else
				{
					$ep = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт ДП/P (Цена акции * Кол-во акций + Цена привелигированной акции * Кол-во привелигированных акций)/(Денежная прибыль * коэффициент)

				if (((float)$CFO[$f][$Iyear]) > 0)
				{
					$capitalization[$f] = (float)$price[$f] * (float)$akcii[$f] + (float)$price_pref[$f] * (float)$akcii_pref[$f];
					@$dpp = number_format ( ((float)$CFO[$f][$Iyear]*(float)$koeficient[$f])*100/($capitalization[$f]) ,2);
				}
				else
				{
					$dpp = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт роста ДП (ДП 2016 / ДП 2011)
				if ((float)$CFO[$f][$Iyear] > 0 && (float)$CFO[$f][$Iyear+5] > 0)
				{
					$cfogr = number_format (( pow((float)$CFO[$f][$Iyear] / (float)$CFO[$f][$Iyear+5],(1/5))-1)*100,2);
				}
				else
				{
					$cfogr = '-';
				}
				if ((float)$CFO[$f][$Iyear+5] < 0 && (float)$CFO[$f][$Iyear] < 0 )
				{
					$cfogr = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт роста выручки (Выручка 2016 / выручка 2011)
				if (((float)$revenue[$f][$Iyear]*(float)$koeficient[$f]) <> 0 && ((float)$revenue[$f][$Iyear+5]*(float)$koeficient[$f]) <> 0 )
				{
					$revgr = number_format (( pow((float)$revenue[$f][$Iyear] / (float)$revenue[$f][$Iyear+5],(1/5))-1)*100,2);
				}
				else
				{
					$revgr = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт роста выручки за год (Выручка 2017 / выручка 2016)
				if (((float)$revenue[$f][$Iyear]*(float)$koeficient[$f]) <> 0)
				{
					@$revgrye = number_format ((((float)$revenue[$f][$Iyear] / (float)$revenue[$f][$Iyear+1])-1)*100,2);
				}
				else
				{
					$revgrye = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт ROДП  (ДП / Активы)
				if (($CFO[$f][$Iyear]) <> 0)
				{
					$rodp = number_format ( $CFO[$f][$Iyear] *100 / ($capital[$f][$Iyear] + $obligation[$f][$Iyear]) ,2);
				}
				else
				{
					$rodp = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт Закредитованности ( Обзяательства /( Капитал + Обязательства))
				if (((float)$capital[$f][$Iyear] + (float)$obligation[$f][$Iyear])   <> 0)
				{
					$dolg=number_format ( (float)$obligation[$f][$Iyear]*100 / ((float)$capital[$f][$Iyear] + (float)$obligation[$f][$Iyear]) ,2);
				}
				else
				{
					$dolg = '-';
				}
//--------------------------------------------------------------------------------------
				// Расчёт Дивидендной доходности ((Дивиденды * Коэффициент ) / (Цена акции * Кол-во акций + Цена привелигированной акции * Кол-во привелигированных акций))
				if ((float)$Dividend[$f] <> 0)
				{
					@$divProfit = number_format ( (float)$Dividend[$f][$Iyear]*100*(float)$koeficient[$f] / ((float)$price[$f] * (float)$akcii[$f] + (float)$price_pref[$f] * (float)$akcii_pref[$f]) ,2);
				}
				else
				{
					$divProfit = '-';
				}
//--------------------------------------------------------------------------------------
				// Ev/ДП ((Капитализация + Обязательства * Коэффициент)/( ДП * Коэффициент))
				if ((float)$CFO[$f][$Iyear] <> 0)
				{
					$EvDp = number_format ( (( (float)$capitalizacia[$f] + (float)$obligation[$f][$Iyear]*(float)$koeficient[$f] ) / ((float)$CFO[$f][$Iyear]*(float)$koeficient[$f])) ,2);
				}
				else
				{
					$EvDp = '-';
				}

//--------------------------------------------------------------------------------------
				// ROE (Чистая прибыль/Капитал)
				if (($freeProfit[$f][$Iyear]) <> 0)
				{
					@$ROE = number_format ( $freeProfit[$f][$Iyear] *100 / $capital[$f][$Iyear+1] ,2);
				}
				else
				{
					$ROE = '-';
				}
//--------------------------------------------------------------------------------------
				// ROA (Чистая прибыль/Капитал)
				if (($freeProfit[$f][$Iyear]) <> 0)
				{
					$ROA = number_format ( $freeProfit[$f][$Iyear] *100 / ($capital[$f][$Iyear] + $obligation[$f][$Iyear]) ,2);
				}
				else
				{
					$ROA = '-';
				}

			// Формирование общей таблицы
			// Заполняем общую таблицу компаний
			$main_tbl[] = array($link[$f],$tikerr[$f],$industry[$f],$price_a,$ep,$dpp,$revgr,$revgrye."%",$cfogr,$ROE,$dolg."%",$divProfit."%",$data);
		  //$main_tbl[] = array($link[$f],$tikerr[$f],$industry[$f],$price_a,$pe,$pdp,$revgr,$cfogr,$ROE,$dolg."%",$divProfit."%",$data);

			$csvFile_tbl = ABSPATH."csv/".$firma[$f].'_tbl.csv';

			@unlink($csvFile_tbl);
			file_put_contents( $csvFile_tbl, $csv_tbl, FILE_APPEND );

			$year_tmp = $year;

			// Формируем таблицу excel для каждой компании
			$csv = "year,c,d,ca,da,r,o,ra,oa,f,cfo,fa,cfoa,ROA,ROE,RDP,a,div,diva,dr,rodp,rodpb,aturnover,ROS,incrASSET,incrCAPITAL,incrREVEN,incrNETPR,incrDP,incrROA,incrROS,incrCFOMargin,incrSHARES,incrDIVID,incrASSETa,incrCAPITALa,incrREVENa,incrNETPRa,incrDPa,incrDIVIDa,divinChP,divinChPa,ChpinDP,buyback \r\n";
			// Формируем таблицу дивидендов
			$div = "year,Dividend,akcii_num \r\n";
				for ($i=8; $i>=0; $i--){
					if ($i==8){	// Первый год приравниваем к "0"
						$rodp=0;
						$rodpb=0;
						$aturnover=0;
						$ROS=0;
						$ROE=0;
						$ROA=0;
					}else{
						@$rodp = round($CFO[$f][$i]*100 / $capital[$f][$i+1],2);									// ROДП (ДП(n) / Капитал(n-1))
						@$rodpb = round($CFO[$f][$i]*100 / ($capital[$f][$i+1]+$obligation[$f][$i+1]),2);			// ROДП (ДП(n) / Активы(n-1))
						@$aturnover = round($revenue[$f][$i] *100 / ($capital[$f][$i]+$obligation[$f][$i]),2);		// Оборачиваемость активов (Выручка(n) / Активы(n))
						@$ROS = round($freeProfit[$f][$i] *100 / $revenue[$f][$i],2);								// ROS (ЧП(n) / Выручка(n))
						@$ROE = round($freeProfit[$f][$i]*100 / $capital[$f][$i+1],2);								// ROE (ЧП(n) / Капитал(n-1))
						@$ROA = round($freeProfit[$f][$i]*100 / ($capital[$f][$i+1]+$obligation[$f][$i+1]),2);		// ROA (ЧП(n) / Активы(n-1))
					};
					$a = (int)$akcii_num[$f][$i];																// Кол-во акций
					$c = round($capital[$f][$i],0);																// Капитал
					$r = round($revenue[$f][$i],0);																// Выручка
					$d = round($obligation[$f][$i],0);															// Обязательства
					@$dr = round( $d *100 / ($d + $c),0);														// Коэффициент финансовой зависимости (уровень закредитованности)
					@$divinChP = round(( $Dividend[$f][$i] / $freeProfit[$f][$i])*100 ,2);						// Доля дивидендов в ЧП
					@$ChpinDP = round(( $freeProfit[$f][$i] / $CFO[$f][$i])*100 ,2);							// Доля ЧП в ДП
//--------------------------------------------------------------------------------
					@$incrASSET = round( ((($capital[$f][$i]+$obligation[$f][$i]) / ($capital[$f][$i+1]+$obligation[$f][$i+1]))-1)*100  ,0);	// Темп роста активов
						if ($incrASSET == '-100')
						{
							$incrASSET = '-';
						}
						if ($capital[$f][$i+1]+$obligation[$f][$i+1] == 0)
						{
							$incrASSET = '-';
						}
//--------------------------------------------------------------------------------
					@$incrCAPITAL = round( (($capital[$f][$i] / $capital[$f][$i+1])-1)*100  ,0);												// Темп роста активов
						if ($incrCAPITAL == '-100')
						{
							$incrCAPITAL = '-';
						}
						if ($capital[$f][$i+1] == 0)
						{
							$incrCAPITAL = '-';
						}
//--------------------------------------------------------------------------------
					@$incrREVEN = round( ((($revenue[$f][$i]) / ($revenue[$f][$i+1]))-1)*100  ,0);												// Темп роста выручки
						if ($incrREVEN == '-100')
						{
							$incrREVEN = 0;
						}
						if ($revenue[$f][$i+1] == 0)
						{
							$incrREVEN = '-';
						}
//--------------------------------------------------------------------------------
					@$incrNETPR = abs(round( ((($freeProfit[$f][$i]) / ($freeProfit[$f][$i+1]))-1)*100  ,0));									// Темп роста чистой прибыли
						if ($incrNETPR == '-100')
						{
							$incrNETPR = 0;
						}
						if ($incrNETPR == '100')
						{
							$incrNETPR = 0;
						}
						if ($freeProfit[$f][$i+1] == 0)
						{
							$incrNETPR = '-';
						}
						if ($freeProfit[$f][$i] > $freeProfit[$f][$i+1])
						{
							$incrNETPR = $incrNETPR;
						}
						else
						{
							$incrNETPR = -$incrNETPR;
						}
//--------------------------------------------------------------------------------
					@$incrDP = abs(round( ((($CFO[$f][$i]) / ($CFO[$f][$i+1]))-1)*100  ,0));													// Темп роста денежной прибыли
						if ($CFO[$f][$i+1] == 0)
						{
							$incrDP = '-';
						}
						if ($CFO[$f][$i] > $CFO[$f][$i+1])
						{
							$incrDP = $incrDP;
						}
						else
						{
							$incrDP = -$incrDP;
						}
//--------------------------------------------------------------------------------
					@$incrROA = round( ((($ROA[$f][$i]) / ($ROA[$f][$i+1]))-1)*100  ,0);														// Темп роста ROA
						if ($incrROA == '-100')
						{
							$incrROA = 0;
						}
//--------------------------------------------------------------------------------
					@$incrROS = round( ((($ROE[$f][$i]) / ($ROE[$f][$i+1]))-1)*100  ,0);														// Темп роста ROS
						if ($incrROS == '-100')
						{
							$incrROS = 0;
						}
//--------------------------------------------------------------------------------
					@$incrCFOMargin = round( ((($rdp[$f][$i]) / ($rdp[$f][$i+1]))-1)*100  ,0);													// Темп роста CFO Margin
						if ($incrCFOMargin == '-100')
						{
							$incrCFOMargin = 0;
						}
//--------------------------------------------------------------------------------
					@$incrSHARES = round( ((($akcii_num[$f][$i]) / ($akcii_num[$f][$i+1]))-1)*100  ,1);											// Темп роста акций
						if ($akcii_num[$f][$i+1] == 0)
						{
							$incrSHARES = '-';
						}
//--------------------------------------------------------------------------------
					@$incrDIVID = round( ((($Dividend[$f][$i]) / ($Dividend[$f][$i+1]))-1)*100  ,0);											// Темп роста дивидендов
						if ($Dividend[$f][$i+1] == 0)
						{
							$incrDIVID = '-';
						}
//--------------------------------------------------------------------------------
//--------------------------------------------------------------------------------
					@$incrASSETa1 = ($capital[$f][$i]+$obligation[$f][$i])/((int)$akcii_num[$f][$i]);
					@$incrASSETa2 = ($capital[$f][$i+1]+$obligation[$f][$i+1])/((int)$akcii_num[$f][$i+1]);
					@$incrASSETa = round( (( $incrASSETa1 / $incrASSETa2 )-1)*100  ,0);
						if ($incrASSETa == '-100')
						{
							$incrASSETa = 0;
						}
						if ($akcii_num[$f][$i+1] == 0)
						{
							$incrASSETa = '-';
						}
						if ($akcii_num[$f][$i] == 0)
						{
							$incrASSETa = '-';
						}
//--------------------------------------------------------------------------------
					@$incrCAPITALa1 = ($capital[$f][$i])/((int)$akcii_num[$f][$i]);
					@$incrCAPITALa2 = ($capital[$f][$i+1])/((int)$akcii_num[$f][$i+1]);
					@$incrCAPITALa = round( (( $incrCAPITALa1 / $incrCAPITALa2 )-1)*100  ,0);
						if ($incrCAPITALa == '-100')
						{
							$incrCAPITALa = 0;
						}
						if ($akcii_num[$f][$i+1] == 0)
						{
							$incrCAPITALa = '-';
						}
						if ($akcii_num[$f][$i] == 0)
						{
							$incrCAPITALa = '-';
						}
//--------------------------------------------------------------------------------
					@$incrREVENa1 = ($revenue[$f][$i])/((int)$akcii_num[$f][$i]);
					@$incrREVENa2 = ($revenue[$f][$i+1])/((int)$akcii_num[$f][$i+1]);
					@$incrREVENa = round( (( $incrREVENa1 / $incrREVENa2 )-1)*100  ,0);
						if ($incrREVENa == '-100')
						{
							$incrREVENa = 0;
						}
						if ($akcii_num[$f][$i+1] == 0)
						{
							$incrREVENa = '-';
						}
						if ($akcii_num[$f][$i] == 0)
						{
							$incrREVENa = '-';
						}
//--------------------------------------------------------------------------------
					@$incrNETPRa1 = ($freeProfit[$f][$i])/((int)$akcii_num[$f][$i]);
					@$incrNETPRa2 = ($freeProfit[$f][$i+1])/((int)$akcii_num[$f][$i+1]);
					@$incrNETPRa = abs( round( (( $incrNETPRa1 / $incrNETPRa2 )-1)*100  ,0));
						if ($incrNETPRa == '-100')
						{
							$incrNETPRa = 0;
						}
						if ($incrNETPRa == '100')
						{
							$incrNETPRa = 0;
						}
						if ($incrNETPRa2 == 0)
						{
							$incrNETPRa = 0;
						}
						if ($akcii_num[$f][$i+1] == 0)
						{
							$incrNETPRa = '-';
						}
						if ($akcii_num[$f][$i] == 0)
						{
							$incrNETPRa = '-';
						}
						if ($freeProfit[$f][$i] > $freeProfit[$f][$i+1])
						{
							$incrNETPRa = $incrNETPRa;
						}
						else
						{
							$incrNETPRa = -$incrNETPRa;
						}
//--------------------------------------------------------------------------------
					@$incrDPa1 = ($CFO[$f][$i])/((int)$akcii_num[$f][$i]);
					@$incrDPa2 = ($CFO[$f][$i+1])/((int)$akcii_num[$f][$i+1]);
					@$incrDPa = abs( round( (( $incrDPa1 / $incrDPa2 )-1)*100  ,0));
						if ($akcii_num[$f][$i+1] == 0)
						{
							$incrDPa = '-';
						}
						if ($akcii_num[$f][$i] == 0)
						{
							$incrDPa = '-';
						}
						if ($CFO[$f][$i] > $CFO[$f][$i+1])
						{
							$incrDPa = $incrDPa;
						}
						else
						{
							$incrDPa = -$incrDPa;
						}
//--------------------------------------------------------------------------------
					@$incrDIVIDa1 = ($Dividend[$f][$i])/((int)$akcii_num[$f][$i]);
					@$incrDIVIDa2 = ($Dividend[$f][$i+1])/((int)$akcii_num[$f][$i+1]);
					@$incrDIVIDa = round( (( $incrDIVIDa1 / $incrDIVIDa2 )-1)*100  ,0);
						if ($akcii_num[$f][$i+1] == 0)
						{
							$incrDIVIDa = '-';
						}
						if ($akcii_num[$f][$i] == 0)
						{
							$incrDIVIDa = '-';
						}
						if ($Dividend[$f][$i] == 0)
						{
							$incrDIVIDa = '-';
						}
						if ($Dividend[$f][$i+1] == 0)
						{
							$incrDIVIDa = '-';
						}
//--------------------------------------------------------------------------------
					// Если кол-во акций = 0 то приравниваем все показатели к 0
					if ($a == '0')
					{
						$ca = 0;											// Капитал на акцию
						$da = 0;											// Обязательства на акцию
						$ra =  0;											// Выручка на акцию
						$oa = 0;											// Операционная прибыль на акцию
						$fa = 0;											// Чистая прибыль на акцию
						$cfoa = 0;											// Денежная прибыль на акцию
						$diva = 0;											// Дивиденды на акцию
						$rdp = 0;
					}
					else
					{
						$ca = round( $c / $a ,2);														// Капитал на акцию
						$da = round(  $d / $a,2);														// Обязательства на акцию
						@$ra = round(	$revenue[$f][$i] / $a,2);										// Выручка на акцию
						@$oa = round(	$operProfit[$f][$i] / $a,2);									// Операционная прибыль на акцию
						@$fa = round(	$freeProfit[$f][$i] / $a,2);									// Чистая прибыль на акцию
						@$cfoa = round(	$CFO[$f][$i] / $a,2);											// Денежная прибыль на акцию
						@$diva = round(	$Dividend[$f][$i] / $a,3);										// Дивиденды на акцию
						@$divinChPa = round(( $Dividend[$f][$i] / $freeProfit[$f][$i] )*100/ $a ,2);	// Доля дивидендов на акцию в ЧП

					}
					// Заполняем таблицу excel по каждой компании
					$csv .= $year_tmp.",".$c.",".$d.",".$ca.",".$da.",".$r.",".round($operProfit[$f][$i],0).",".$ra.",".$oa.",".round($freeProfit[$f][$i],0).",".round($CFO[$f][$i],0).",".$fa.",".$cfoa.",".$ROA.",".$ROE.",".round(($rdp*100),2).",".$a.",".round($Dividend[$f][$i],0).",".$diva.",".$dr.",".$rodp.",".$rodpb.",".$aturnover.",".$ROS.",".$incrASSET.",".$incrCAPITAL.",".$incrREVEN.",".$incrNETPR.",".$incrDP.",".$incrROA.",".$incrROS.",".$incrCFOMargin.",".$incrSHARES.",".$incrDIVID.",".$incrASSETa.",".$incrCAPITALa.",".$incrREVENa.",".$incrNETPRa.",".$incrDPa.",".$incrDIVIDa.",".$divinChP.",".$divinChPa.",".$ChpinDP.",".$buyback[$f][$i]." \r\n";
					// Заполняем таблицу дивидендов
					$div .=  ($year_tmp).",".$Dividend[$f][$i].",".$akcii_num[$f][$i]."\r\n";
					$year_tmp++;
				}
				// Сохраняем файл с названием компании
				$csvFile = ABSPATH."csv/".$firma[$f].'.csv';
			@unlink($csvFile);
			file_put_contents( $csvFile, $csv, FILE_APPEND );
			}
//--------------------------------------------------------------------------------------------------------------------------------------------------!!
			// Запись в общую таблицу компаний
			if ($_POST['rus_company'])
			{
				$CID = "38465"; echo"<br> Обновлена таблица Российских компаний.";
			}
			if ($_POST['ex_company'])
			{
				$CID = "33305";	echo"<br> Обновлена таблица Американских компаний.";
			}
			if ($_POST['eur_company'])
			{
				$CID = "38075";	echo"<br> Обновлена таблица Европейских компаний.";
			}

			// Подключение базы данных
			$mysql = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD);
			// Устанавливание базы данных для выполняемых запросов
			mysqli_select_db($mysql,DB_NAME);
			// Выполнение запроса к базе данных (установка кодировки названий)
			mysqli_query($mysql,"SET NAMES utf8");
			// Возвращение JSON-представления данных
			$encode_tbl = json_encode($main_tbl, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
			// Формирование запроса в базу данных на замену данных в общей таблице для компаний
			$sql = "UPDATE `".DB_NAME."`.`wp_posts` SET `post_content` = '".mysqli_real_escape_string($mysql,$encode_tbl)."' WHERE `wp_posts`.`ID` = ".$CID;
			// Запрос в базу данных на заменение
			mysqli_query($mysql,$sql);
   }
   else	// Если файл excel не загружен
   {
      echo("Ошибка загрузки файла");
   }
}

// Быстрое создание графиков
function bez_create_chart2() {

	// Получение списка файлов и каталогов
	$arr_csv = scandir (ABSPATH."/csv");
	// Цикл перебирает массив, задаваемый с помощью $arr_csv. На каждой итерации значение текущего элемента присваивается переменной $csv_name
	foreach ($arr_csv as $csv_name) {
		if ((strpos($csv_name,"_tbl")=='0')AND(strpos($csv_name,"_div")=='0')AND($csv_name<>'.')AND($csv_name<>'..')) {
			$arr_options[] = substr($csv_name,0,(strlen($csv_name)-4));
		}
	}

// Выбор валюты
$valuta = "руб.";
$mvaluta = "млн.руб.";
	if (isset($_POST['valuta'])) {
		if ($_POST['valuta']=='USD') {
			$mvaluta = "млн.долл.";
			$valuta = "долл.";
		}
		if ($_POST['valuta']=='RUR') {
			$mvaluta = "млн.руб.";
			$valuta = "руб.";
		}
		if ($_POST['valuta']=='EUR') {
			$mvaluta = "млн.евро";
			$valuta = "евро";
		}
	}

// Выбор тикера
	if (isset($_POST['ticket'])) {
		$company_tiket = str_replace(' ','',$_POST['ticket']);
	}

// шаблоны создаваемых графиков
		$java_txt[1] = "var chart1 = AmCharts.makeChart(\"chart-01\",
{
	\"type\": \"serial\",
	\"hideCredits\":true,
	\"theme\": \"dark\",
	\"sequencedAnimation\": false,
	\"depth3D\": 10,
	\"angle\": 30,
	\"autoMarginOffset\": 40,
	\"thousandsSeparator\": \" \",
	\"fontSize\": 14,
	\"marginLeft\": 100,	// Чтобы не двигалась ось значений,
	\"marginRight\": 100,	// Чтобы не двигалась ось значений
	\"colors\" :
[
	\"#9060bf\",
	\"#8f8fee\",
	\"#9060bf\",
	\"#8f8fee\",
],
	\"legend\":
{
	\"position\": \"bottom\",
	\"valueWidth\": 100,
	\"valueAlign\": \"left\",
	\"equalWidths\": true,
	\"valueText\": \"[[value]]%\",
},
\"dataLoader\":
{
	\"url\": \"/csv/".$company_tiket.".csv\",
	\"format\": \"csv\",
	\"showCurtain\": true,
	\"showErrors\": true,
	\"async\": true,
	\"reload\": 0,
	\"timestamp\": true,
	\"delimiter\": \",\",
	\"useColumnNames\": true
	},
\"valueAxes\":
[{
	\"id\": \"ValueAxis-1\",
	\"stackType\": \"regular\",
	\"minMaxMultiplier\": -0.1,
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
},{
	\"id\": \"ValueAxis-2\",
	\"position\": \"right\",
	\"stackType\": \"regular\",
	\"minMaxMultiplier\": -0.1,
	\"maximum\": 0,
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
}],

\"graphs\":
[{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Капитал: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#FFCC33\",
	\"negativeLineColor\": \"#FFCC33\",
	\"valueField\": \"c\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrCAPITAL]] %\"
},{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Обязательства: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#CC0000\",
	\"negativeLineColor\": \"#CC0000\",
	\"valueField\": \"d\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,									// Отступ от столбцов
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"top\",
	\"labelText\": \"[[incrASSET]] %\"
},{
	\"legendValueText\": \"[[value]] ".$valuta."\",
	\"balloonText\": \"[[value]]\",
	\"newStack\": true,
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Капитал на акцию: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#FFCC00\",
	\"negativeLineColor\": \"#FFCC00\",
	\"valueField\": \"ca\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrCAPITALa]] %\"
},{
	\"legendValueText\": \"[[value]] ".$valuta."\",
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Обязательства на акцию: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#990000\",
	\"negativeLineColor\": \"#990000\",
	\"valueField\": \"da\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,									// Отступ от столбцов
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"top\",
	\"labelText\": \"[[incrASSETa]] %\"
},{
	\"legendValueText\": \"[[value]] %\",
	\"balloonText\": \"[[value]]\",
	\"title\": \"Уровень закредитованности: \",
	\"type\": \"line\",
	\"lineColor\": \"#FF0000\",						// Задаём цвет линии выше уровня
	\"legendColor\": \"#9900ff\",					// Принудительно задаём цвет линии в легенде
	\"lineThickness\": 4,
	\"valueAxis\": \"ValueAxis-2\",
	\"negativeBase\": 100,						// Задаём уровень, ниже которого будет другой цвет
	\"negativeLineColor\": \"#9900ff\",				// Задаём цвет линии ниже уровня
	\"valueField\": \"dr\"
}],
\"categoryField\": \"year\",
\"categoryAxis\":
{
	\"gridPosition\": \"start\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"position\": \"left\"
},
\"chartCursor\":
{
	\"cursorAlpha\": 0,
}

});
	chart1.addListener(\"rendered\", zoomChart1);

	zoomChart1();
	function zoomChart1() {
		chart1.zoomToIndexes(0,8);
	}
	chart1.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[2] = "var chart2 = AmCharts.makeChart(\"chart-02\",
{
	\"type\": \"serial\",
	\"hideCredits\":true,
	\"theme\": \"dark\",
	\"sequencedAnimation\": false,
	\"depth3D\": 10,
	\"angle\": 30,
	\"autoMarginOffset\": 40,
	\"thousandsSeparator\": \" \",
	\"fontSize\": 14,
	\"marginLeft\": 100,	// Чтобы не двигалась ось значений
	\"colors\" :
[
	\"#9060bf\",
	\"#8f8fee\",
	\"#9060bf\",
	\"#8f8fee\"
],
	\"legend\":
{
	\"position\": \"bottom\",
	\"valueWidth\": 100,
	\"valueAlign\": \"left\",
	\"equalWidths\": true,
},
\"dataLoader\":
{
	\"url\": \"/csv/".$company_tiket.".csv\",
	\"format\": \"csv\",
	\"showCurtain\": true,
	\"showErrors\": true,
	\"async\": true,
	\"reload\": 0,
	\"timestamp\": true,
	\"delimiter\": \",\",
	\"useColumnNames\": true
	},
\"valueAxes\":
[{
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
}],

\"graphs\":
[{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Выручка: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#FFCC33\",
	\"negativeLineColor\": \"#FFCC33\",
	\"valueField\": \"r\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,									// Отступ от столбцов
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"middle\",
	\"labelAnchor\": \"start\",
	\"labelText\": \"[[incrREVEN]] %\",
},{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Операционная прибыль: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#CC0000\",
	\"negativeLineColor\": \"#CC0000\",
	\"valueField\": \"o\",
},{
	\"legendValueText\": \"[[value]] ".$valuta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Выручка на акцию: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#FFCC00\",
	\"negativeLineColor\": \"#FFCC00\",
	\"valueField\": \"ra\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,									// Отступ от столбцов
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrREVENa]] %\",
},{
	\"legendValueText\": \"[[value]] ".$valuta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Операционная прибыль на акцию: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#990000\",
	\"negativeLineColor\": \"#990000\",
	\"valueField\": \"oa\",
}],
\"categoryField\": \"year\",
\"categoryAxis\":
{
	\"gridPosition\": \"start\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"position\": \"left\"
},
\"chartCursor\":
{
	\"cursorAlpha\": 0,
}

});

	chart2.addListener(\"rendered\", zoomChart2);
	zoomChart2();
	function zoomChart2() {
		chart2.zoomToIndexes(0,8);
	}

	chart2.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[3]="var chart3 = AmCharts.makeChart(\"chart-03\",
{
	\"type\": \"serial\",
	\"hideCredits\":true,
	\"theme\": \"dark\",
	\"sequencedAnimation\": false,
	\"depth3D\": 10,
	\"angle\": 30,
	\"autoMarginOffset\": 40,
	\"thousandsSeparator\": \" \",
	\"fontSize\": 14,
	\"marginLeft\": 100,	// Чтобы не двигалась ось значений
	\"colors\" :
[
	\"#9060bf\",
	\"#8f8fee\",
	\"#9060bf\",
	\"#8f8fee\"
],
	\"legend\":
{
	\"position\": \"bottom\",
	\"valueWidth\": 100,
	\"valueAlign\": \"left\",
	\"equalWidths\": true,
},
\"dataLoader\":
{
	\"url\": \"/csv/".$company_tiket.".csv\",
	\"format\": \"csv\",
	\"showCurtain\": true,
	\"showErrors\": true,
	\"async\": true,
	\"reload\": 0,
	\"timestamp\": true,
	\"delimiter\": \",\",
	\"useColumnNames\": true
	},
\"valueAxes\":
[{
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
}],

\"graphs\":
[{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Чистая прибыль:\",
	\"type\": \"column\",
	\"negativeFillColors\": \"#FFCC33\",
	\"negativeLineColor\": \"#FFCC33\",
	\"valueField\": \"f\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,
	\"labelRotation\": 270,
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrNETPR]]%\",
},{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Денежный поток:\",
	\"type\": \"column\",
	\"negativeFillColors\": \"#CC0000\",
	\"negativeLineColor\": \"#CC0000\",
	\"valueField\": \"cfo\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,
	\"labelRotation\": 270,
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrDP]]%\",
},{
	\"legendValueText\": \"[[value]] ".$valuta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Чистая прибыль на акцию:\",
	\"type\": \"column\",
	\"negativeFillColors\": \"#FFCC00\",
	\"negativeLineColor\": \"#FFCC00\",
	\"valueField\": \"fa\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,
	\"labelRotation\": 270,
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrNETPRa]]%\",
},{
	\"legendValueText\": \"[[value]] ".$valuta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Денежный поток на акцию:\",
	\"type\": \"column\",
	\"negativeFillColors\": \"#990000\",
	\"negativeLineColor\": \"#990000\",
	\"valueField\": \"cfoa\",
	\"labelOffset\": 15,
	\"labelRotation\": 270,
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrDPa]]%\",
}],
\"categoryField\": \"year\",
\"categoryAxis\":
{
	\"gridPosition\": \"start\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"position\": \"left\"
},
\"chartCursor\":
{
	\"cursorAlpha\": 0,
}

});
	chart3.addListener(\"rendered\", zoomChart3);

	zoomChart3();
	function zoomChart3() {
		chart3.zoomToIndexes(0,8);
	}
	chart3.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[4]="var chart4 = AmCharts.makeChart(\"chart-04\",
{
	\"type\": \"serial\",
	\"hideCredits\":true,
	\"theme\": \"dark\",
	\"sequencedAnimation\": false,
	\"depth3D\": 10,
	\"angle\": 30,
	\"autoMarginOffset\": 40,
	\"thousandsSeparator\": \" \",
	\"fontSize\": 14,
	\"marginLeft\": 100,	// Чтобы не двигалась ось значений
	\"colors\" :
[
	\"#9060bf\",
	\"#8f8fee\",
	\"#b98bb6\",
	\"#67c49b\",
	\"#f7b6d6\",
],
	\"legend\":
{
	\"position\": \"bottom\",
	\"valueWidth\": 100,
	\"valueAlign\": \"left\",
	\"equalWidths\": true,
	\"valueText\": \"[[value]]%\",
},
\"dataLoader\":
{
	\"url\": \"/csv/".$company_tiket.".csv\",
	\"format\": \"csv\",
	\"showCurtain\": true,
	\"showErrors\": true,
	\"async\": true,
	\"reload\": 0,
	\"timestamp\": true,
	\"delimiter\": \",\",
	\"useColumnNames\": true
	},
\"valueAxes\":
[{
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
}],

\"graphs\":
[{
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Оборач-ть активов (Выручка/Активы): \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#cc0000\",
	\"negativeLineColor\": \"#cc0000\",
	\"valueField\": \"aturnover\",
},{
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"ROS (ЧП/Выручка): \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#cc0000\",
	\"negativeLineColor\": \"#cc0000\",
	\"valueField\": \"ROS\",
},{
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"ROE (ЧП/Капитал): \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#cc0000\",
	\"negativeLineColor\": \"#cc0000\",
	\"valueField\": \"ROE\",
},{
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"ROA (ЧП/Активы): \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#cc0000\",
	\"negativeLineColor\": \"#cc0000\",
	\"valueField\": \"ROA\",
},{
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"ROДП (ДП/Капитал): \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#cc0000\",
	\"negativeLineColor\": \"#cc0000\",
	\"valueField\": \"rodp\",
}],
\"categoryField\": \"year\",
\"categoryAxis\":
{
	\"gridPosition\": \"start\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"position\": \"left\"
},
\"chartCursor\":
{
	\"cursorAlpha\": 0,
}

});
	chart4.addListener(\"rendered\", zoomChart4);

	zoomChart4();
	function zoomChart4() {
		chart4.zoomToIndexes(0,8);
	}
	chart4.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[5]="var chart5 = AmCharts.makeChart(\"chart-05\",
{
	\"type\": \"serial\",
	\"hideCredits\":true,
	\"theme\": \"dark\",
	\"sequencedAnimation\": false,
	\"depth3D\": 10,
	\"angle\": 30,
	\"autoMarginOffset\": 40,
	\"thousandsSeparator\": \" \",
	\"fontSize\": 14,
	\"marginLeft\": 100,	// Чтобы не двигалась ось значений
	\"colors\" :
[
	\"#9060bf\",
	\"#8f8fee\",
],
	\"legend\":
{
	\"position\": \"bottom\",
	\"valueWidth\": 100,
	\"valueAlign\": \"left\",
	\"equalWidths\": true,
	\"valueText\": \"[[value]]%\",
},
\"dataLoader\":
{
	\"url\": \"/csv/".$company_tiket.".csv\",
	\"format\": \"csv\",
	\"showCurtain\": true,
	\"showErrors\": true,
	\"async\": true,
	\"reload\": 0,
	\"timestamp\": true,
	\"delimiter\": \",\",
	\"useColumnNames\": true
	},
\"valueAxes\":
[{
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
	\"minMaxMultiplier\": -0.2,
}],

\"graphs\":
[{
	\"legendValueText\": \"[[value]] млн.шт.\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Всего акций: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#ff4f00\",
	\"negativeLineColor\": \"#ff4f00\",
	\"color\": \"#000000\",
	\"valueField\": \"a\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,									// Отступ от столбцов
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrSHARES]] %\"
},{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"title\": \"Выкуп(-)/выпуск(+) акций: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#ff4f00\",
	\"negativeLineColor\": \"#ff4f00\",
	\"color\": \"#000000\",
	\"valueField\": \"buyback\",
}],
\"categoryField\": \"year\",
\"categoryAxis\":
{
	\"gridPosition\": \"start\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"position\": \"left\"
},
\"chartCursor\":
{
	\"cursorAlpha\": 0,
}

});
	chart5.addListener(\"rendered\", zoomChart5);

	zoomChart5();
	function zoomChart5() {
		chart5.zoomToIndexes(0,8);
	}
		chart5.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[6] = "var chart6 = AmCharts.makeChart(\"chart-06\",
{
	\"type\": \"serial\",
	\"hideCredits\":true,
	\"theme\": \"dark\",
	\"sequencedAnimation\": false,
	\"depth3D\": 10,
	\"angle\": 30,
	\"autoMarginOffset\": 40,
	\"thousandsSeparator\": \" \",
	\"fontSize\": 14,
	\"marginLeft\": 100,	// Чтобы не двигалась ось значений,
	\"marginRight\": 100,	// Чтобы не двигалась ось значений
	\"colors\" :
[
	\"#9060bf\",
	\"#8f8fee\",
],
	\"legend\":
{
	\"position\": \"bottom\",
	\"valueWidth\": 100,
	\"valueAlign\": \"left\",
	\"equalWidths\": true,
	\"valueText\": \"[[value]]%\",
},
\"dataLoader\":
{
	\"url\": \"/csv/".$company_tiket.".csv\",
	\"format\": \"csv\",
	\"showCurtain\": true,
	\"showErrors\": true,
	\"async\": true,
	\"reload\": 0,
	\"timestamp\": true,
	\"delimiter\": \",\",
	\"useColumnNames\": true
	},
\"valueAxes\":
[{
	\"id\": \"ValueAxis-1\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
	\"minimum\": 0,
},{
	\"id\": \"ValueAxis-2\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"position\": \"right\",
	\"ignoreAxisWidth\": true,	// Чтобы не двигалась ось значений
	\"minimum\": 0,
}],

\"graphs\":
[{
	\"legendValueText\": \"[[value]] ".$mvaluta."\",
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"id\": \"g 1\",
	\"title\": \"Дивиденды: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#ff4f00\",
	\"negativeLineColor\": \"#ff4f00\",
	\"color\": \"#000000\",
	\"valueField\": \"div\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,									// Отступ от столбцов
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrDIVID]] %\",
},{
	\"legendValueText\": \"[[value]] ".$valuta."\",
	\"balloonText\": \"[[value]]\",
	\"fillAlphas\": 0.8,
	\"lineAlpha\": 0.3,
	\"id\": \"g2\",
	\"title\": \"Дивиденды на акцию: \",
	\"type\": \"column\",
	\"negativeFillColors\": \"#4f4f00\",
	\"negativeLineColor\": \"#4f4f00\",
	\"color\": \"#000000\",
	\"valueField\": \"diva\",
	\"showAllValueLabels\": true,							// Показать все значения величин
	\"labelOffset\": 15,									// Отступ от столбцов
	\"labelRotation\": 270,								// Поворот значения - горизонтально
	\"labelPosition\": \"middle\",
	\"labelText\": \"[[incrDIVIDa]] %\",
},{
	\"legendValueText\": \"[[value]] %\",
	\"balloonText\": \"[[value]]\",
	\"title\": \"Доля дивидендов в ЧП: \",
	\"type\": \"line\",
	\"lineColor\": \"#FF0000\",						// Задаём цвет линии выше уровня
	\"legendColor\": \"#9900ff\",					// Принудительно задаём цвет линии в легенде
	\"lineThickness\": 4,
	\"valueAxis\": \"ValueAxis-2\",
	\"negativeBase\": 100,						// Задаём уровень, ниже которого будет другой цвет
	\"negativeLineColor\": \"#9900ff\",				// Задаём цвет линии ниже уровня
	\"valueField\": \"divinChP\"
}],
\"categoryField\": \"year\",
\"categoryAxis\":
{
	\"gridPosition\": \"start\",
	\"axisAlpha\": 0,
	\"gridAlpha\": 0,
	\"position\": \"left\"
},
\"chartCursor\":
{
	\"cursorAlpha\": 0,
}

});
	chart6.addListener(\"rendered\", zoomChart6);

	zoomChart6();
	function zoomChart6() {
		chart6.zoomToIndexes(0,8);
	}
		chart6.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

$java_txt_divid = "var chart7 = AmCharts.makeChart(\"chart-07\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
			\"useColumnNames\": true
		 },
		\"addClassNames\" : true,
		\"rotate\": false,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3264c9\",\"#599400\",\"#fc9700\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},
	   \"graphs\": [{
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"id\": \"g1-1\",
		   \"title\": \"Дивиденды об.\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#ff4f00\",
			\"negativeLineColor\": \"#ff4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"div\"
		},
        {
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"id\": \"g1-2\",
		   \"title\": \"Дивиденды прив.\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#ff4f00\",
			\"negativeLineColor\": \"#ff4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"da\"
		}],
		\"valueAxes\": [
			{
				\"id\": \"ValueAxis-1\",
			}
		],
		\"allLabels\": [],
		\"titles\": [],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueText\": \"[[value]] ".$mvaluta."\",
		   \"valueWidth\": 190,
		   \"valueAlign\": \"left\",
		   \"equalWidths\": false,
		   \"periodValueText\": \"Сумма: [[value.sum]] ".$mvaluta."\"
		 },
		 \"chartCursor\": {
		   \"cursorAlpha\": 0
		 }
		});

	chart7.addListener(\"rendered\", zoomChart7);

	zoomChart7();
	function zoomChart7() {
		chart7.zoomToIndexes(0,8);
	}
		chart7.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	if (isset($_POST['force'])) {
		if ($_POST['force']==$_POST['ticket']) {
			$mysql = mysqli_connect(DB_HOST, DB_USER,DB_PASSWORD, DB_NAME);
			mysqli_select_db($mysql,DB_NAME);
			mysqli_query($mysql,"SET NAMES utf8");
				for ($i=1; $i<=6; $i++) {
					$post_title = $_POST['force']."_".$i;
					$post_name = urlencode ($_POST['force']."_".$i);
					$query = "DELETE FROM wp_posts WHERE post_title = '".$post_title."'";
					@$res = mysqli_fetch_array(mysqli_query($mysql,$query));
					echo "<p>Удалено, записываем по новой.</p>";
					$_POST['graph'] = $_POST['force'];
				}
		}
	}

	if (!isset($_POST['ticket'])) {
		Echo "
		<h2><p><b> Укажите Tiker фирмы, желательно без пробелов: </b></p></h2>
		<form action=\"\" method=\"post\">
		<select name=\"ticket\">";
		foreach ($arr_options as $option) {
			echo "<option value=\"".$option."\">".$option."</option>";
		}
		echo"</select>";
		echo"<select name=\"valuta\">
			<option value=\"USD\">USD</option>
			<option value=\"руб.\">RUB</option>
			<option value=\"EUR\">EUR</option>
			</select>
		<input type=\"hidden\" name=\"graph\" value=\"1\"><br>
		<input type=\"submit\" value=\"Создать\"><br>
		</form>";
	} else {
		if (isset($_POST['graph'])) {
			$company_tiket = str_replace(' ','',$_POST['ticket']);
			// Подключаемся к базе данных
			$mysql = mysqli_connect(DB_HOST, DB_USER,DB_PASSWORD, DB_NAME);
			// Выбираем базу данных
			mysqli_select_db($mysql,DB_NAME);
			// Устанавливаем кодировку названий
			mysqli_query($mysql,"SET NAMES utf8");
			$meta_value2 = "//www.amcharts.com/lib/3/amcharts.js
							//www.amcharts.com/lib/3/plugins/dataloader/dataloader.min.js
							//www.amcharts.com/lib/3/serial.js";
			$err = 0;
			$i = 1;
			for ($i=1; $i<=6; $i++) {
				$post_title = $company_tiket."_".$i;
				$post_name = urlencode ($company_tiket."_".$i);
				$meta_value3 = "<div id=\"chart-0".$i."\" style=\"width:100%; height:500px\"></div>";
				$query = "SELECT id FROM wp_posts WHERE post_title = '".$post_title."'";
				$res = mysqli_fetch_array(mysqli_query($mysql,$query));
				if ($res['id'] == '') {
					$query = "INSERT INTO wp_posts (id,post_author,post_date,post_date_gmt,post_content,post_title,post_status,comment_status,ping_status,post_password,post_name,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count)
					VALUES (NULL,32,now(),now(),'','".$post_title."','publish','closed','closed','','".$post_name."',now(),now(),'','0','http://vanin-invest.com/?post_type=amchart','0','amchart','','0')";
					$res = mysqli_query($mysql,$query);
					$post_id = mysqli_insert_id($mysql);
					echo "<p> График создан: id= ".$post_id."</p>";
					$query1 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_slug','".$post_title."')";
					$res = mysqli_query($mysql,$query1);
					$query2 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_resources','".$meta_value2."')";
					$res = mysqli_query($mysql,$query2);
					$query3 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_html','".$meta_value3."')";
					$res = mysqli_query($mysql,$query3);
					$query4 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_javascript','".$java_txt[$i]."')";
					$res = mysqli_query($mysql,$query4);
				} else {
					$err = 3; echo "<p>уже есть график id=".$res['id']."</p>";
				}
			}
			if ($err==3) {	// График уже есть
				echo "<form method=\"post\">
				<input type=\"text\" name=\"ticket\" readonly value=\"".$company_tiket."\">
				<input type=\"hidden\" name=\"force\" value=\"".$company_tiket."\">
				<input type=\"hidden\" name=\"valuta\" value=\"".$_POST['valuta']."\">
				<input type=\"submit\" name=\"submit\" value=\"Перезаписать принудительно\"><br>
				</form>";
			}
			echo"<form method=\"post\">
			<input type=\"submit\" name=\"submit\" value=\"Продолжить\"><br>
			</form>";
		}
		if (isset($_POST['divid'])) {
			$post_title = $company_tiket."_6";
			$query = "SELECT id FROM wp_posts WHERE post_title = '".$post_title."'";
			$res = mysqli_query($query);
			$row = mysqli_fetch_array($res);
			$post_id = $row['id'];
			$query = "UPDATE wp_postmeta SET  meta_value = '".$java_txt_divid."' WHERE meta_key = '_amcharts_javascript' AND post_id  = '".$post_id."'";
			$res = mysqli_query($query);
			echo "<h2>График обновлен.</h2>";
		}
	}
}

// Редактирование графиков
function bez_edit_chart2() {
	// Подключение к базе данных
	$mysql = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD);
	// Выбор базы данных
	mysqli_select_db($mysql,DB_NAME);
	// Запрос в базу данных на изменение кодировки названий
	mysqli_query($mysql,"SET NAMES utf8");
echo "<p>ВНИМАНИЕ! Изменения коснуться ВСЕХ выбранных графиков.</p>";
echo "<p>Изменения НЕОБРАТИМЫ.</p>";
echo "<p>В загружаемом шаблоне испльзуйте метки @valuta@ и @ticket@ для указания мест вставки руб.; долл.; евро и названия компании (ссылка на файл).</p>";
echo "<p>Для обновления 4-го графика (рентабельность) необходимо выбрать USD. Графики обновятся на всех валютах.</p>";

// Запрос на очистку вспомогательной таблицы wp_edit_all_graph
mysqli_query($mysql,"TRUNCATE TABLE `wp_edit_all_graph`");

// Обновление базы данных компаний (1-6) с рублями
for ($i=1; $i<=6; $i++)
{
	// Формирование запроса - выделение из таблицы wp_postmeta базы данных post_id и meta_value со значениями meta_value chart№ руб.
	$query = "SELECT `post_id`,`meta_value` FROM `wp_postmeta` WHERE `meta_key` = '_amcharts_javascript' AND `meta_value` LIKE '%chart".$i."%руб.%'";
	// Запрос в базу данных
	$result = mysqli_query($mysql,$query);
	// Заполнение параметров таблицы wp_edit_all_graph компаниями выделенными с помощью запроса
	while ($row = mysqli_fetch_array($result))
	{
		list($id,$str) = $row;
		$startpos = strpos($str,'/csv/')+5;
		$str = substr ($str,$startpos,$startpos+20);
		$stoppos = strpos($str,'.csv');
		$ticket = substr ($str,0,$stoppos);
		$q = "INSERT INTO `wp_edit_all_graph` VALUES (".$id.",'".$ticket."','RUR','".$i."')";
		$res = mysqli_query($mysql,$q);
	}
}

// Обновление базы данных компаний (1-6) с долл
for ($i=1; $i<=6; $i++)
{
	// Формирование запроса - выделение из таблицы wp_postmeta базы данных post_id и meta_value со значениями meta_value chart№ долл.
	$query = "SELECT `post_id`,`meta_value` FROM `wp_postmeta` WHERE `meta_key` = '_amcharts_javascript' AND `meta_value` LIKE '%chart".$i."%долл.%'";
	// Запрос в базу данных
	$result = mysqli_query($mysql,$query);
	// Заполнение параметров таблицы wp_edit_all_graph компаниями выделенными с помощью запроса
	while ($row = mysqli_fetch_array($result))
	{
		list($id, $str) = $row;
		$startpos = strpos($str,'/csv/')+5;
		$str = substr ($str,$startpos,$startpos+20);
		$stoppos = strpos($str,'.csv');
		$ticket = substr ($str,0,$stoppos);
		$q = "INSERT INTO `wp_edit_all_graph` VALUES (".$id.",'".$ticket."','USD','".$i."')";
		$res = mysqli_query($mysql,$q);
	}
}

// Обновление базы данных компаний (1-6) с евро
for ($i=1; $i<=6; $i++)
{
	// Формирование запроса - выделение из таблицы wp_postmeta базы данных post_id и meta_value со значениями meta_value chart№ евро
	$query = "SELECT `post_id`,`meta_value` FROM `wp_postmeta` WHERE `meta_key` = '_amcharts_javascript' AND `meta_value` LIKE '%chart".$i."%евро%'";
	// Запрос в базу данных
	$result = mysqli_query($mysql,$query);
	// Заполнение параметров таблицы wp_edit_all_graph компаниями выделенными с помощью запроса
	while ($row = mysqli_fetch_array($result))
	{
		list($id, $str) = $row;
		$startpos = strpos($str,'/csv/')+5;
		$str = substr ($str,$startpos,$startpos+20);
		$stoppos = strpos($str,'.csv');
		$ticket = substr ($str,0,$stoppos);
		$q = "INSERT INTO `wp_edit_all_graph` VALUES (".$id.",'".$ticket."','EUR','".$i."')";
		$res = mysqli_query($mysql,$q);
	}
}

// Обновление базы данных компаний (4) с %
$i=4;
	// Формирование запроса - выделение из таблицы wp_postmeta базы данных post_id и meta_value со значениями meta_value chart4
	$query = "SELECT `post_id`,`meta_value` FROM `wp_postmeta` WHERE `meta_key` = '_amcharts_javascript' AND `meta_value` LIKE '%chart4%'";
	// Запрос в базу данных
	$result = mysqli_query($mysql,$query);
	// Заполнение параметров таблицы wp_edit_all_graph компаниями выделенными с помощью запроса
	while ($row = mysqli_fetch_array($result))
	{
		list($id, $str) = $row;
		$startpos = strpos($str,'/csv/')+5;
		$str = substr ($str,$startpos,$startpos+20);
		$stoppos = strpos($str,'.csv');
		$ticket = substr ($str,0,$stoppos);
		$q = "INSERT INTO `wp_edit_all_graph` VALUES (".$id.",'".$ticket."','USD','".$i."')";
		$res = mysqli_query($mysql,$q);
	}


/* Если стерли названия компаний можно восстановить из предыдущих графиков */
/*
$query = "SELECT `id`,`company_ticket` FROM `wp_edit_all_graph` WHERE `company_ticket` = '' ";
	$result = mysqli_query($mysql,$query);
	while ($row = mysqli_fetch_array($result)) {
		list($id, $str) = $row;
			$query2 = "SELECT `id`,`company_ticket` FROM `wp_edit_all_graph` WHERE `id` = '".($id-1)."' ";
			$result2 = mysqli_query($mysql,$query2);
			$row2 = mysqli_fetch_array($result2);
			list($id2, $ticket) = $row2;
		$q = "UPDATE `wp_edit_all_graph` SET `company_ticket`='".$ticket."' WHERE `id` = '".$id."'";
echo $q."<br/>";
		$res = mysqli_query($mysql,$q);
	}

*/

if (isset($_POST['graphval']))
{
	$filtr = '1';
	if ($_POST['lang'] == 'USD')
	{
		$filtr = "`lang`='USD' ";
		$valuta='долл.';
	}
	elseif ($_POST['lang'] == 'RUR')
	{
		$filtr = "`lang`='RUR' ";
		$valuta='руб.';
	}
	else
	{
		$filtr = "`lang`='EUR' ";
		$valuta='евро';
	}
	$filtr .= "AND `number`='".$_POST['graphN']."'";
	$query = "SELECT `id`, `company_ticket` FROM `wp_edit_all_graph` WHERE ".$filtr;
	$res = mysqli_query($mysql,$query);
	$post_id = '';
	$newgraph = $_POST['graphval'];
	while ($row = mysqli_fetch_array($res))
	{
		$post_id = $row[0];
		$ticket = $row[1];
		$newgraph_tmp = str_replace  ('@ticket@',$ticket,$newgraph);
		$newgraph_tmp = str_replace  ('@valuta@',$valuta,$newgraph_tmp);
		$newgraph_tmp = $newgraph_tmp;
		$query2 = "UPDATE wp_postmeta SET  meta_value = '".$newgraph_tmp."' WHERE meta_key = '_amcharts_javascript' AND post_id = '".$post_id."'";
		$res2 = mysqli_query($mysql,$query2);
		echo "<h2>График обновлен.   ".$post_id."</h2>";
	}
}
echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<textarea name=\"graphval\" rows=\"20\" cols=\"150\"></textarea>";
echo "Номер графика <select name=\"graphN\"><option value=\"1\">1</option><option value=\"2\">2</option><option value=\"3\">3</option><option value=\"4\">4</option><option value=\"5\">5</option><option value=\"6\">6</option></select>";
echo "Валюта <select name=\"lang\"><option value=\"USD\">USD</option><option value=\"RUR\">RUR</option><option value=\"EUR\">EUR</option></select>";
echo "<input type=\"submit\" value=\"Применить\">";
echo "</form>";

}

// Создание нескольких графиков
function bez_create_chart3() {
	//
	$arr_csv = scandir (ABSPATH."/csv");
	foreach ($arr_csv as $csv_name)
	{
		if ((strpos($csv_name,"_tbl")=='0')AND(strpos($csv_name,"_div")=='0')AND($csv_name<>'.')AND($csv_name<>'..'))
		{
			$arr_options[] = substr($csv_name,0,(strlen($csv_name)-4));
		}
	}
// Выбор валюты
$valuta = "руб.";
$mvaluta = "млн.руб.";
	if (isset($_POST['valuta']))
	{
		if ($_POST['valuta']=='USD')
		{
			$mvaluta = "млн.долл.";
			$valuta = "долл.";
		}
		if ($_POST['valuta']=='RUR')
		{
			$mvaluta = "млн.руб.";
			$valuta = "руб.";
		}
		if ($_POST['valuta']=='EUR')
		{
			$mvaluta = "млн.евро";
			$valuta = "евро";
		}
	}
// Выбор тикера
	if (isset($_POST['ticket']))
	{
		$company_tiket = str_replace(' ','',$_POST['ticket']);
	}

// шаблоны создаваемых графиков
		$java_txt[1] = "var chart1 = AmCharts.makeChart(\"chart-01\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
			\"useColumnNames\": true
		 },
		\"addClassNames\" : true,
		\"rotate\": false,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3366CC\",\"#669900\",\"#003399\",\"#336600\",\"#9900ff\",\"#FF0000\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},
			\"valueAxes\": [{
			\"id\": \"ValueAxis-1\",
			\"stackType\": \"regular\"
		},
		{
			\"id\": \"ValueAxis-2\",
			\"position\": \"right\",
			\"stackType\": \"regular\"
		}],
		\"graphs\": [{
			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Капитал\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#FFCC33\",
			\"negativeLineColor\": \"#FFCC33\",
			\"valueField\": \"c\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelOffset\": 15,									// Отступ от столбцов
			\"labelRotation\": 270,									// Поворот значения - горизонтально
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrCAPITAL]] %\"
		},{
			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Обязательства\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#CC0000\",
			\"negativeLineColor\": \"#CC0000\",
			\"valueField\": \"d\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelOffset\": 15,									// Отступ от столбцов
			\"labelRotation\": 270,									// Поворот значения - горизонтально
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrASSET]] %\"
		},{
			\"legendValueText\": \"[[value]] ".$valuta."\",
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Капитал на акцию\",
			\"type\": \"column\",
			\"newStack\": true,
			\"negativeFillColors\": \"#FFCC00\",
			\"negativeLineColor\": \"#FFCC00\",
			\"valueField\": \"ca\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelOffset\": 15,									// Отступ от столбцов
			\"labelRotation\": 270,									// Поворот значения - горизонтально
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrCAPITALa]] %\"
		}, {
			\"legendValueText\": \"[[value]] ".$valuta."\",
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Обязательства на акцию\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#990000\",
			\"negativeLineColor\": \"#990000\",
			\"valueField\": \"da\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelOffset\": 15,									// Отступ от столбцов
			\"labelRotation\": 270,									// Поворот значения - горизонтально
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrASSETa]] %\"
		},{
			\"legendValueText\": \"[[value]] \%\",
			\"balloonText\": \"[[value]]\",
			\"title\": \"Уровень закредитованности\",
			\"type\": \"line\",
			\"lineColor\": \"#FF0000\",						// Задаём цвет линии выше уровня
			\"legendColor\": \"#9900ff\",					// Принудительно задаём цвет линии в легенде
			\"lineThickness\": 4,
			\"valueAxis\": \"ValueAxis-2\",
			\"negativeBase\": 100,						// Задаём уровень, ниже которого будет другой цвет
			\"negativeLineColor\": \"#9900ff\",				// Задаём цвет линии ниже уровня
			\"valueField\": \"dr\",
			\"showAllValueLabels\": true,
			\"stackType\": \"regular\"							// Показать все значения величин
		}],

		\"allLabels\": [],
		\"titles\": [],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueWidth\": 100,
		   \"valueAlign\": \"left\",
		   \"equalWidths\": true,
		   \"periodValueText\": \" \"
		 },
		 \"chartCursor\": {
		   \"cursorAlpha\": 0
		 }
		});
	chart1.addListener(\"rendered\", zoomChart1);

	zoomChart1();
	function zoomChart1() {
		chart1.zoomToIndexes(0,8);
	}
	chart1.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[2] = "var chart2 = AmCharts.makeChart(\"chart-02\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
			\"useColumnNames\": true
		 },
		\"mouseWheelScrollEnabled\": true,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"marginRight\": 60,
		\"marginTop\": 10,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3366CC\",\"#669900\",\"#003399\",\"#336600\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},
		\"graphs\": [{
			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Выручка\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#FFCC33\",
			\"negativeLineColor\": \"#FFCC33\",
			\"valueField\": \"r\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelPosition\": \"top\",
			\"labelAnchor\": \"start\",
			\"labelText\": \"[[incrREVEN\]] %\",
		}, {
			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Операционная прибыль\",
			\"type\": \"column\",
			\"newStack\": true,
			\"negativeFillColors\": \"#CC0000\",
			\"negativeLineColor\": \"#CC0000\",
			\"valueField\": \"o\",
		},{
			\"legendValueText\": \"[[value]] ".$valuta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Выручка на акцию\",
			\"type\": \"column\",
			\"newStack\": true,
			\"negativeFillColors\": \"#FFCC00\",
			\"negativeLineColor\": \"#FFCC00\",
			\"valueField\": \"ra\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrREVENa\]] %\",
		}, {
			\"legendValueText\": \"[[value]] ".$valuta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Операционная прибыль на акцию\",
			\"type\": \"column\",
			\"newStack\": true,
			\"negativeFillColors\": \"#990000\",
			\"negativeLineColor\": \"#990000\",
			\"valueField\": \"oa\",
		}],
		\"valueAxes\": [
			{
				\"axisAlpha\": 0.3,
				\"gridAlpha\": 0,
				\"stackType\": \"regular\",
				\"id\": \"ValueAxis-1\",
			}
		],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueAlign\": \"left\",
		   \"periodValueText\": \" \"
		 },
			\"chartCursor\": {
		   \"cursorAlpha\": 0
		 }

		});

	chart2.addListener(\"rendered\", zoomChart2);
	zoomChart2();
	function zoomChart2() {
		chart2.zoomToIndexes(0,8);
	}

	chart2.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[3]="var chart3 = AmCharts.makeChart(\"chart-03\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
			\"useColumnNames\": true
		 },
		\"rotate\": false,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3366CC\",\"#669900\",\"#003399\",\"#336600\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},
		\"graphs\": [{
			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Чистая прибыль\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#FFCC33\",
			\"negativeLineColor\": \"#FFCC33\",
			\"valueField\": \"f\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelOffset\": 15,
			\"labelRotation\": 270,
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrNETPR\]]%\",
		}, {
			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Денежный поток\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#CC0000\",
			\"negativeLineColor\": \"#CC0000\",
			\"valueField\": \"cfo\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelOffset\": 15,
			\"labelRotation\": 270,
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrDP\]]%\",
		}, {
			\"legendValueText\": \"[[value]] ".$valuta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Чистая прибыль на акцию\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#FFCC00\",
			\"negativeLineColor\": \"#FFCC00\",
			\"valueField\": \"fa\",
			\"showAllValueLabels\": true,							// Показать все значения величин
			\"labelOffset\": 15,
			\"labelRotation\": 270,
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrNETPRa\]]%\",
		}, {
			\"legendValueText\": \"[[value]] ".$valuta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Денежный поток на акцию\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#990000\",
			\"negativeLineColor\": \"#990000\",
			\"valueField\": \"cfoa\",
			\"labelOffset\": 15,
			\"labelRotation\": 270,
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrDPa\]]%\",
		}],
		\"valueAxes\": [
			{
			  \"axisAlpha\": 0.3,
			  \"gridAlpha\": 0,
			 \"id\": \"ValueAxis-1\",
			}
		],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueWidth\": 100,
		   \"valueAlign\": \"left\",
		   \"equalWidths\": true,
		   \"periodValueText\": \" \"
		 },
		   \"chartCursor\": {
		   \"cursorAlpha\": 0
		 }
		});
	chart3.addListener(\"rendered\", zoomChart3);

	zoomChart3();
	function zoomChart3() {
		chart3.zoomToIndexes(0,8);
	}
	chart3.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[4]="var chart4 = AmCharts.makeChart(\"chart-04\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
		  \"zoomOutText\": \"\",
			\"useColumnNames\": true
		 },
		\"addClassNames\" : true,
		\"rotate\": false,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3366CC\",\"#669900\",\"#FFCC33\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},

		\"graphs\": [{
			\"balloonText\": \"[[value]] %\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Оборач-ть активов (Выручка/Активы): \",
			\"type\": \"column\",
			\"negativeFillColors\": \"#cc0000\",
			\"negativeLineColor\": \"#cc0000\",
			\"valueField\": \"aturnover\",
		}, {
			\"balloonText\": \"[[value]] %\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"ROS (ЧП/Выручка): \",
			\"type\": \"column\",
			\"negativeFillColors\": \"#cc0000\",
			\"negativeLineColor\": \"#cc0000\",
			\"valueField\": \"ROS\",
		}, {
			\"balloonText\": \"[[value]] %\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"ROE (ЧП/Капитал): \",
			\"type\": \"column\",
			\"negativeFillColors\": \"#cc0000\",
			\"negativeLineColor\": \"#cc0000\",
			\"valueField\": \"ROE\",
		}, {
			\"balloonText\": \"[[value]] %\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"ROA (ЧП/Активы): \",
			\"type\": \"column\",
			\"negativeFillColors\": \"#cc0000\",
			\"negativeLineColor\": \"#cc0000\",
			\"valueField\": \"ROA\",
		}, ],
		\"allLabels\": [],
		\"titles\": [],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueText\": \"[[value]]%\",
		   \"valueWidth\": 190,
		   \"valueAlign\": \"left\",
		   \"equalWidths\": false,
		   \"periodValueText\": \" \"
		 },
		 \"chartCursor\": {
		   \"cursorAlpha\": 0
		 },
		});

	chart4.addListener(\"rendered\", zoomChart4);

	zoomChart4();
	function zoomChart4() {
		chart4.zoomToIndexes(0,8);
	}
	chart4.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";


	$java_txt[5]="var chart5 = AmCharts.makeChart(\"chart-05\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
			\"useColumnNames\": true
		 },
		\"addClassNames\" : true,
		\"rotate\": false,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3264c9\",\"#599400\",\"#fc9700\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},
	   \"graphs\": [{
   			\"legendValueText\": \"[[value]] млн.шт\",
			\"balloonText\": \"[[value]] млн.шт\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Акций: \",
			\"type\": \"column\",
			\"negativeFillColors\": \"#ff4f00\",
			\"negativeLineColor\": \"#ff4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"a\",
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrSHARES\]] %\",
		},{
			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"balloonText\": \"[[value]] ".$mvaluta."\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"title\": \"Выкуп/выпуск акций: \",
			\"type\": \"column\",
			\"newStack\": true,
			\"negativeFillColors\": \"#ff4f00\",
			\"negativeLineColor\": \"#ff4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"buyback\",
		}],
		\"valueAxes\": [
			{
				\"id\": \"ValueAxis-1\",
				\"stackType\": \"regular\",
			}
		],
		\"allLabels\": [],
		\"titles\": [],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueWidth\": 160,
		   \"valueAlign\": \"left\",
		   \"equalWidths\": false,
		   \"periodValueText\": \" \"
		 },
		 \"chartCursor\": {
		   \"cursorAlpha\": 0
		 }
		});
	chart5.addListener(\"rendered\", zoomChart5);

	zoomChart5();
	function zoomChart5() {
		chart5.zoomToIndexes(0,8);
	}
		chart5.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	$java_txt[6] = "var chart6 = AmCharts.makeChart(\"chart-06\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
			\"useColumnNames\": true
		 },
		\"addClassNames\" : true,
		\"rotate\": false,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3264c9\",\"#599400\",\"#fc9700\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},
	   \"graphs\": [{
   			\"legendValueText\": \"[[value]] ".$mvaluta."\",
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"id\": \"g1\",
		   \"title\": \"Дивиденды\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#ff4f00\",
			\"negativeLineColor\": \"#ff4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"div\",
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrDIVID\]] %\",
		},{
   			\"legendValueText\": \"[[value]] ".$valuta."\",
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"id\": \"g2\",
		   \"title\": \"Дивиденды на акцию\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#4f4f00\",
			\"negativeLineColor\": \"#4f4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"diva\",
			\"labelPosition\": \"top\",
			\"labelText\": \"[[incrDIVIDa\]] %\",
		}],
		\"valueAxes\": [
			{
				\"id\": \"ValueAxis-1\",
				\"stackType\": \"regular\",
			}
		],
		\"allLabels\": [],
		\"titles\": [],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueWidth\": 190,
		   \"valueAlign\": \"left\",
		   \"equalWidths\": false,
		   \"periodValueText\": \" \"
		 },
		 \"chartCursor\": {
		   \"cursorAlpha\": 0
		 }
		});

	chart6.addListener(\"rendered\", zoomChart6);

	zoomChart6();
	function zoomChart6() {
		chart6.zoomToIndexes(0,8);
	}
		chart6.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

$java_txt_divid = "var chart7 = AmCharts.makeChart(\"chart-07\",
	 {
		\"type\": \"serial\",
		\"zoomOutText\": \"\",
		\"dataLoader\": {
			\"url\": \"/csv/".$company_tiket.".csv\",
			\"format\": \"csv\",
			\"showCurtain\": true,
			\"showErrors\": true,
			\"async\": true,
			\"reload\": 0,
			\"timestamp\": true,
			\"delimiter\": \",\",
			\"useColumnNames\": true
		 },
		\"addClassNames\" : true,
		\"rotate\": false,
		\"autoMarginOffset\": 40,
		\"startDuration\": 1,
		\"fontSize\": 14,
		\"color\": \"#FFFFFF\",
		\"colors\" : [\"#3264c9\",\"#599400\",\"#fc9700\"],
		\"categoryField\": \"year\",
		\"categoryAxis\": {
			\"gridPosition\": \"start\"
		},
	   \"graphs\": [{
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"id\": \"g1-1\",
		   \"title\": \"Дивиденды об.\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#ff4f00\",
			\"negativeLineColor\": \"#ff4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"div\"
		},
        {
			\"balloonText\": \"[[value]]\",
			\"fillAlphas\": 0.8,
			\"lineAlpha\": 0.3,
			\"id\": \"g1-2\",
		   \"title\": \"Дивиденды прив.\",
			\"type\": \"column\",
			\"negativeFillColors\": \"#ff4f00\",
			\"negativeLineColor\": \"#ff4f00\",
			\"color\": \"#000000\",
			\"valueField\": \"da\"
		}],
		\"valueAxes\": [
			{
				\"id\": \"ValueAxis-1\",
			}
		],
		\"allLabels\": [],
		\"titles\": [],
		 \"legend\": {
		   \"position\": \"bottom\",
		   \"valueText\": \"[[value]] ".$mvaluta."\",
		   \"valueWidth\": 190,
		   \"valueAlign\": \"left\",
		   \"equalWidths\": false,
		   \"periodValueText\": \"Сумма: [[value.sum]] ".$mvaluta."\"
		 },
		 \"chartCursor\": {
		   \"cursorAlpha\": 0
		 }
		});

	chart7.addListener(\"rendered\", zoomChart7);

	zoomChart7();
	function zoomChart7() {
		chart7.zoomToIndexes(0,8);
	}
		chart7.numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};";

	if (isset($_POST['force']))
	{
		if ($_POST['force']==$_POST['ticket'])
		{
		$mysql = mysqli_connect(DB_HOST, DB_USER,DB_PASSWORD, DB_NAME);
		mysqli_select_db($mysql,DB_NAME);
		mysqli_query($mysql,"SET NAMES utf8");
			for ($i=1; $i<=6; $i++)
			{
				$post_title = $_POST['force']."_".$i;
				$post_name = urlencode ($_POST['force']."_".$i);
				$query = "DELETE FROM wp_posts WHERE post_title = '".$post_title."'";
				@$res = mysqli_fetch_array(mysqli_query($mysql,$query));
				echo "<p>Удалено, записываем по новой.</p>";
				$_POST['graph'] = $_POST['force'];
			}
		}
	}

	if (!isset($_POST['ticket']))
	{
		Echo "
		<h2><p><b> Укажите Tiker фирмы, желательно без пробелов: </b></p></h2>
		<form action=\"\" method=\"post\">
		<select name=\"ticket\">";
		foreach ($arr_options as $option)
		{
			echo "<option value=\"".$option."\">".$option."</option>";
		}
		echo"</select>";
		echo"<select name=\"valuta\">
			<option value=\"USD\">USD</option>
			<option value=\"руб.\">RUB</option>
			<option value=\"EUR\">EUR</option>
			</select>
		<input type=\"hidden\" name=\"graph\" value=\"1\"><br>
		<input type=\"submit\" value=\"Создать\"><br>
		</form>";
	}
	else
	{
		if (isset($_POST['graph']))
		{
			$company_tiket = str_replace(' ','',$_POST['ticket']);
			// Подключаемся к базе данных
			$mysql = mysqli_connect(DB_HOST, DB_USER,DB_PASSWORD, DB_NAME);
			// Выбираем базу данных
			mysqli_select_db($mysql,DB_NAME);
			// Устанавливаем кодировку названий
			mysqli_query($mysql,"SET NAMES utf8");
			$meta_value2 = "//www.amcharts.com/lib/3/amcharts.js
							//www.amcharts.com/lib/3/plugins/dataloader/dataloader.min.js
							//www.amcharts.com/lib/3/serial.js";
			$err = 0;
			$i = 1;
			for ($i=1; $i<=6; $i++)
			{
				$post_title = $company_tiket."_".$i;
				$post_name = urlencode ($company_tiket."_".$i);
				$meta_value3 = "<div id=\"chart-0".$i."\" style=\"width:100%; height:500px\"></div>";
				$query = "SELECT id FROM wp_posts WHERE post_title = '".$post_title."'";
				$res = mysqli_fetch_array(mysqli_query($mysql,$query));
				if ($res['id'] == '')
				{
					$query = "INSERT INTO wp_posts (id,post_author,post_date,post_date_gmt,post_content,post_title,post_status,comment_status,ping_status,post_password,post_name,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count)
					VALUES (NULL,32,now(),now(),'','".$post_title."','publish','closed','closed','','".$post_name."',now(),now(),'','0','http://vanin-invest.com/?post_type=amchart','0','amchart','','0')";
					$res = mysqli_query($mysql,$query);
					$post_id = mysqli_insert_id($mysql);
					echo "<p> График создан: id= ".$post_id."</p>";
					$query1 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_slug','".$post_title."')";
					$res = mysqli_query($mysql,$query1);
					$query2 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_resources','".$meta_value2."')";
					$res = mysqli_query($mysql,$query2);
					$query3 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_html','".$meta_value3."')";
					$res = mysqli_query($mysql,$query3);
					$query4 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value) VALUES (NULL,'".$post_id."','_amcharts_javascript','".$java_txt[$i]."')";
					$res = mysqli_query($mysql,$query4);
				}
				else
				{
					$err = 3; echo "<p>уже есть график id=".$res['id']."</p>";
				}
			}
			if ($err==3)	// График уже есть
			{
				echo "<form method=\"post\">
				<input type=\"text\" name=\"ticket\" readonly value=\"".$company_tiket."\">
				<input type=\"hidden\" name=\"force\" value=\"".$company_tiket."\">
				<input type=\"hidden\" name=\"valuta\" value=\"".$_POST['valuta']."\">
				<input type=\"submit\" name=\"submit\" value=\"Перезаписать принудительно\"><br>
				</form>";
			}
			echo"<form method=\"post\">
			<input type=\"submit\" name=\"submit\" value=\"Продолжить\"><br>
			</form>";
		}
		if (isset($_POST['divid']))
		{
			$post_title = $company_tiket."_6";
			$query = "SELECT id FROM wp_posts WHERE post_title = '".$post_title."'";
			$res = mysql_query($query);
			$row = mysql_fetch_array($res);
			$post_id = $row['id'];
			$query = "UPDATE wp_postmeta SET  meta_value = '".$java_txt_divid."' WHERE meta_key = '_amcharts_javascript' AND post_id  = '".$post_id."'";
			$res = mysql_query($query);
			echo "<h2>График обновлен.</h2>";
		}
	}
}

// Загрузка и создание графиков облигаций
function bez_load_ob2() {
	$columns = 8; // Колонок в экселе

	// Подключение к базе данных
	$mysql = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD);
	// Выбор базы данных
	mysqli_select_db($mysql,DB_NAME);
	// Значения
	$meta_value2 = "
//www.amcharts.com/lib/3/amcharts.js
//www.amcharts.com/lib/3/serial.js
//www.amcharts.com/lib/3/themes/dark.js
//www.amcharts.com/lib/3/amcharts.js
//www.amcharts.com/lib/3/xy.js
	";
	// Значения
	$html = "<div id=\"".$chartdiv."\" style=\"height:300px\"></div>";

	if (!isset($_POST['test']))	{
	Echo "
      <h2><p><b> Форма для загрузки файлов </b></p></h2>
      <form action=\"\" method=\"post\" enctype=\"multipart/form-data\">
      <input type=\"file\" name=\"filename\"><br>
      <input type=\"hidden\" name=\"test\" value=\"1\">
	  <input type=\"submit\" value=\"Загрузить\"><br>
      </form>
</body>
</html>";
	} else {

   // Проверяем загружен ли файл

	if(@is_uploaded_file($_FILES["filename"]["tmp_name"])) {
		if(@$_FILES["filename"]["size"] > 1024*4*1024) {
			echo ("Размер файла превышает три мегабайта");
			exit;
		}
	if($_FILES["filename"]["type"] != "application/vnd.ms-excel") {
		echo "Формат файла должен быть Excel csv, разделенный запятыми.";
		exit;
	}
	@mkdir(ABSPATH."uploads", 0777);
	$file = ABSPATH."uploads/".$_FILES["filename"]["name"];
    move_uploaded_file($_FILES["filename"]["tmp_name"], $file);
	echo "файл успешно загружен<br/>";

	$row = 0;
	$handle = fopen($file, "r");
	echo $file."<br/>";
		while (($data = fgetcsv($handle, 9000, ";")) !== FALSE) {
			if ($row>1000) {break;}
			for ($i=0;$i<$columns;$i++) {
				if ($data[$i*3]<>'') {
			//	ECHO "text[".$i."][".$row."] =".iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i*3])));
					$text[$i][$row] = iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i*3])));
					$y[$i][$row]	= iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i*3+1])));
					$x[$i][$row]	= iconv("CP1251", "UTF-8", str_replace(',','.',str_replace(' ','',$data[$i*3+2])));
				}
			}
			$row++;
		}
			@fclose($file);
			@unlink($file);
	} else {
		echo("Ошибка загрузки файла");
	}
	$rowMax = $row;
	for ($i=0;$i<$columns;$i++) {
		$dataProvider[$i] ='';
		$graph[$i] = '';
	}
	for ($row=1;$row<=$rowMax;$row++) {
		for ($i=0;$i<$columns;$i++) {
			if (isset($text[$i][$row])) {
				$dataProvider[$i] .= "
					\"text".$row."\": \"".$text[$i][$row]."\",
					\"y".$row."\": ".$y[$i][$row].",
					\"x".$row."\": ".$x[$i][$row].",
					";
				$graph[$i] .= "
				 {
					\"bullet\": \"circle\",
					\"valueField\": \"value\",
					\"bulletSize\": 25,
					\"balloonText\": \"<p><b>[[text".$row."]]:</b> <br/> [[x]] дн [[y]] % </p>\",
					\"xField\": \"x".$row."\",
					\"yField\": \"y".$row."\",
				  }, ";
			}
		}
	}

	for ($i=0;$i<$columns;$i++) {
	$java_txt[$i+1] = "
		 var chart".($i+1)." = AmCharts.makeChart( \"chartdiv_".($i+1)."\", {
		  \"type\": \"xy\",
		  \"hideCredits\":true,
		  \"theme\": \"dark\",
		  \"balloon\":{
		   \"fixedPosition\":true,
		  },
		  \"dataProvider\": [ {

		  ".$dataProvider[$i]."

		  },],
		  \"valueAxes\": [ {
			\"position\": \"bottom\",
			\"axisAlpha\": 0,
            \"title\": \"дней до погашения\",
		  }, {
			\"minMaxMultiplier\": 1.2,
			\"axisAlpha\": 0,
			\"position\": \"left\",
            \"title\": \"доходность\",

		  } ],
		  \"graphs\": [

		   ".$graph[$i]."

		   ],
		  \"marginLeft\": 46,
		  \"marginBottom\": 35,
		} );
		chart".($i+1).".numberFormatter = {
				decimalSeparator : \",\",
				thousandsSeparator : \" \"
			};	";

			$post_title = "Obligation_N".($i+1);
			$post_name = urlencode ($company_tiket."_N".($i+1));
			$meta_value3 = "<div id=\"chartdiv_".($i+1)."\" style=\"width:100%; height:500px\"></div>";
			$query = "SELECT id FROM wp_posts WHERE post_title = '".$post_title."'";
			$res = mysqli_fetch_array(mysqli_query($mysql,$query));
			if ($res['id'] == '') {
				$query = "INSERT INTO wp_posts (id,post_author,post_date,post_date_gmt,post_content,post_title,post_status,comment_status,ping_status,post_password,post_name,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count)
				VALUES (NULL,32,now(),now(),'','".$post_title."','publish','closed','closed','','".$post_name."',now(),now(),'','0','http:/vanin-invest.com/?post_type=amchart','0','amchart','','0')";
				$res = mysqli_query($mysql,$query);
				$post_id = mysqli_insert_id($mysql);
				echo "<p> График создан: id= ".$post_id."</p>";
				$query1 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value)
				VALUES (NULL,'".$post_id."','_amcharts_slug','".$post_title."')";
				$res = mysqli_query($mysql,$query1);
				$query2 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value)
				VALUES (NULL,'".$post_id."','_amcharts_resources','".$meta_value2."')";
				$res = mysqli_query($mysql,$query2);
				$query3 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value)
				VALUES (NULL,'".$post_id."','_amcharts_html','".$meta_value3."')";
				$res = mysqli_query($mysql,$query3);
				$query4 = "INSERT INTO wp_postmeta (meta_id,post_id,meta_key,meta_value)
				VALUES (NULL,'".$post_id."','_amcharts_javascript','".$java_txt[$i+1]."')";
				$res = mysqli_query($mysql,$query4);
			} else {
				$err = 3; echo "<p>уже есть график id=".$res['id']."</p>";
			}
		}
	}
}

?>
