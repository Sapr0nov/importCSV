<?php
/**
	 * Plugin Name: BeZ_table
	 * Plugin URI: https://stacksite.ru
	 * Description: Add table from csv file
	 * Version: 1.1
	 * Author: Sapronov Sergey
	 * Author URI: https://stacksite.ru
	 */


$path = $_SERVER['DOCUMENT_ROOT'];
 // подключаем
include_once $path . '/wp-config.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';
global $wpdb;  // теперь переменная $wpdb доступна

//	include_once 'functions.php';

	function formatN($any_string) {
		$any_string= round($any_string,5);
		$p = strpos($any_string,'.');
		$l = strlen($any_string);
		if ($p==0)  {
			return 0;
		}
		else {
			return ($l-$p-1);
		}
	}

	function tbl_div($arr) {
	ob_start();
	if (!isset($arr['valuta'])) {$arr['valuta'] = 'руб.';}
	$file= ABSPATH."csv/".$arr['name']."_tbl.csv";
	$handle = @fopen($file, "r");

	while (($data = @fgetcsv($handle, 9000, ",")) !== FALSE) {
	if ($data[0]=='') {break;}
	$row++;
	list($tiket,$firma,$akcii_price,$akcii,$akcii_pref_price,$akcii_pref,$koef) = $data;
	}
	$N_akcii_price = 5;
	$N_akcii_pref_price = 5;

	$kap = $akcii*$akcii_price;
	$kap_pref = $akcii_pref*$akcii_pref_price;
	$sum = $kap_pref+$kap;
	is_float($kap) ? $N_kap = 0 : $N_kap = 0;
	is_float($kap_pref) ? $N_kap_pref = 0 : $N_kap_pref = 0;
	is_float($sum) ? $N_sum = 0 : $N_sum = 0;
	$N_akcii_price = formatN($akcii_price);
	$N_akcii_pref_price = formatN($akcii_pref_price);
	 ?>


	<div class="wpb_wrapper"><div class="vc_tta-container" data-vc-action="collapse">
	<div class="vc_general vc_tta vc_tta-tabs vc_tta-color-grey vc_tta-style-classic vc_tta-shape-rounded vc_tta-o-shape-group pricing-table-wrapper vc_tta-tabs-position-top vc_tta-controls-align-center pricing-table-wrapper">
	<div class="vc_tta-tabs-container">
	<ul class="vc_tta-tabs-list">
	<li class="vc_tta-tab vc_active" data-vc-tab="">
	<a href="#1473974856888-8b163078-424b" data-vc-tabs="" data-vc-container=".vc_tta"><span class="vc_tta-title-text">Стоимость компании</span></a></li>
	<li class="vc_tta-tab" data-vc-tab="">
	<a href="#1475845052246-ba7b667c-20d1" data-vc-tabs="" data-vc-container=".vc_tta"><span class="vc_tta-title-text">Стоимость компании</span></a></li></ul></div>
	<div class="vc_tta-panels-container">
	<div class="vc_tta-panels">
	<div class="vc_tta-panel pricing-table-button vc_active" id="1473974856888-8b163078-424b" data-vc-content=".vc_tta-panel-body">
	<div class="vc_tta-panel-heading"><h4 class="vc_tta-panel-title">
	<a href="#1473974856888-8b163078-424b" data-vc-accordion="" data-vc-container=".vc_tta-container"><span class="vc_tta-title-text">Стоимость компании</span></a></h4></div><div class="vc_tta-panel-body" style=""></div></div>
	<div class="vc_tta-panel" id="1475845052246-ba7b667c-20d1" data-vc-content=".vc_tta-panel-body"><div class="vc_tta-panel-heading"><h4 class="vc_tta-panel-title">
	<a href="#1475845052246-ba7b667c-20d1" data-vc-accordion="" data-vc-container=".vc_tta-container"><span class="vc_tta-title-text">Стоимость компании</span></a>
	</h4></div><div class="vc_tta-panel-body" style="">
		<div class="wpb_text_column wpb_content_element ">
			<div class="wpb_wrapper">
				<div class="">


	<input type="hidden" id="table_1_desc" value="{&quot;tableId&quot;:&quot;table_1&quot;,&quot;selector&quot;:&quot;#table_1&quot;,&quot;responsive&quot;:false,&quot;editable&quot;:false,&quot;inlineEditing&quot;:false,&quot;popoverTools&quot;:false,&quot;hideBeforeLoad&quot;:false,&quot;number_format&quot;:2,&quot;decimal_places&quot;:2,&quot;spinnerSrc&quot;:&quot;http:\/\/cfocom.ru\/wp-content\/plugins\/wpdatatables\/assets\/\/img\/spinner.gif&quot;,&quot;groupingEnabled&quot;:false,&quot;tableWpId&quot;:&quot;16&quot;,&quot;dataTableParams&quot;:{&quot;sDom&quot;:&quot;BT\u003C\u0022clear\u0022\u003Elftip&quot;,&quot;bSortCellsTop&quot;:false,&quot;bFilter&quot;:true,&quot;bPaginate&quot;:false,&quot;columnDefs&quot;:[{&quot;sType&quot;:&quot;string&quot;,&quot;wdtType&quot;:&quot;string&quot;,&quot;className&quot;:&quot; \u0412\u0438\u0434 \u0430\u043a\u0446\u0438\u0439&quot;,&quot;bVisible&quot;:true,&quot;bSortable&quot;:true,&quot;searchable&quot;:true,&quot;InputType&quot;:&quot;&quot;,&quot;name&quot;:&quot;\u0412\u0438\u0434 \u0430\u043a\u0446\u0438\u0439&quot;,&quot;origHeader&quot;:&quot;\u0412\u0438\u0434 \u0430\u043a\u0446\u0438\u0439&quot;,&quot;notNull&quot;:false,&quot;conditionalFormattingRules&quot;:[],&quot;aTargets&quot;:[0]},{&quot;sType&quot;:&quot;formatted-num&quot;,&quot;wdtType&quot;:&quot;int&quot;,&quot;className&quot;:&quot;numdata integer  \u041a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e &quot;,&quot;bVisible&quot;:true,&quot;bSortable&quot;:true,&quot;searchable&quot;:true,&quot;InputType&quot;:&quot;&quot;,&quot;name&quot;:&quot;\u041a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e &quot;,&quot;origHeader&quot;:&quot;\u041a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e &quot;,&quot;notNull&quot;:false,&quot;conditionalFormattingRules&quot;:[],&quot;aTargets&quot;:[1]},{&quot;sType&quot;:&quot;string&quot;,&quot;wdtType&quot;:&quot;string&quot;,&quot;className&quot;:&quot; \u0426\u0435\u043d\u0430, \u0440\u0443\u0431.&quot;,&quot;bVisible&quot;:true,&quot;bSortable&quot;:true,&quot;searchable&quot;:true,&quot;InputType&quot;:&quot;&quot;,&quot;name&quot;:&quot;\u0426\u0435\u043d\u0430, \u0440\u0443\u0431.&quot;,&quot;origHeader&quot;:&quot;\u0426\u0435\u043d\u0430, \u0440\u0443\u0431.&quot;,&quot;notNull&quot;:false,&quot;conditionalFormattingRules&quot;:[],&quot;aTargets&quot;:[2]},{&quot;sType&quot;:&quot;formatted-num&quot;,&quot;wdtType&quot;:&quot;int&quot;,&quot;className&quot;:&quot;numdata integer  \u041a\u0430\u043f\u0438\u0442\u0430\u043b\u0438\u0437\u0430\u0446\u0438\u044f, \u0440\u0443\u0431.&quot;,&quot;bVisible&quot;:true,&quot;bSortable&quot;:true,&quot;searchable&quot;:true,&quot;InputType&quot;:&quot;&quot;,&quot;name&quot;:&quot;\u041a\u0430\u043f\u0438\u0442\u0430\u043b\u0438\u0437\u0430\u0446\u0438\u044f, \u0440\u0443\u0431.&quot;,&quot;origHeader&quot;:&quot;\u041a\u0430\u043f\u0438\u0442\u0430\u043b\u0438\u0437\u0430\u0446\u0438\u044f, \u0440\u0443\u0431.&quot;,&quot;notNull&quot;:false,&quot;conditionalFormattingRules&quot;:[],&quot;aTargets&quot;:[3]}],&quot;bAutoWidth&quot;:false,&quot;bSort&quot;:false,&quot;oLanguage&quot;:{&quot;sProcessing&quot;:&quot;\u041f\u043e\u0434\u043e\u0436\u0434\u0438\u0442\u0435...&quot;,&quot;sLengthMenu&quot;:&quot;\u041f\u043e\u043a\u0430\u0437\u0430\u0442\u044c _MENU_ \u0437\u0430\u043f\u0438\u0441\u0435\u0439&quot;,&quot;sZeroRecords&quot;:&quot;\u0417\u0430\u043f\u0438\u0441\u0438 \u043e\u0442\u0441\u0443\u0442\u0441\u0442\u0432\u0443\u044e\u0442.&quot;,&quot;sInfo&quot;:&quot;\u0417\u0430\u043f\u0438\u0441\u0438 \u0441 _START_ \u0434\u043e _END_ \u0438\u0437 _TOTAL_ \u0437\u0430\u043f\u0438\u0441\u0435\u0439&quot;,&quot;sInfoEmpty&quot;:&quot;\u0417\u0430\u043f\u0438\u0441\u0438 \u0441 0 \u0434\u043e 0 \u0438\u0437 0 \u0437\u0430\u043f\u0438\u0441\u0435\u0439&quot;,&quot;sInfoFiltered&quot;:&quot;(\u043e\u0442\u0444\u0438\u043b\u044c\u0442\u0440\u043e\u0432\u0430\u043d\u043e \u0438\u0437 _MAX_ \u0437\u0430\u043f\u0438\u0441\u0435\u0439)&quot;,&quot;sInfoPostFix&quot;:&quot;&quot;,&quot;sSearch&quot;:&quot;\u041f\u043e\u0438\u0441\u043a:&quot;,&quot;sUrl&quot;:&quot;&quot;,&quot;oPaginate&quot;:{&quot;sFirst&quot;:&quot;\u041f\u0435\u0440\u0432\u0430\u044f&quot;,&quot;sPrevious&quot;:&quot;\u041f\u0440\u0435\u0434\u044b\u0434\u0443\u0449\u0430\u044f&quot;,&quot;sNext&quot;:&quot;\u0421\u043b\u0435\u0434\u0443\u044e\u0449\u0430\u044f&quot;,&quot;sLast&quot;:&quot;\u041f\u043e\u0441\u043b\u0435\u0434\u043d\u044f\u044f&quot;}},&quot;buttons&quot;:[],&quot;bProcessing&quot;:false,&quot;sPaginationType&quot;:&quot;full_numbers&quot;,&quot;oSearch&quot;:{&quot;bSmart&quot;:false,&quot;bRegex&quot;:false,&quot;sSearch&quot;:&quot;&quot;}},&quot;serverSide&quot;:false,&quot;columnsFixed&quot;:0,&quot;advancedFilterEnabled&quot;:false,&quot;datepickFormat&quot;:&quot;dd\/mm\/yy&quot;,&quot;tabletWidth&quot;:&quot;1280&quot;,&quot;mobileWidth&quot;:&quot;480&quot;}">
	<div id="table_1_wrapper" class="wpDataTables wpDataTablesWrapper no-footer"><div class="clear"></div><div id="table_1_filter" class="dataTables_filter"><label>Поиск:<input type="search" class="" placeholder="" aria-controls="table_1"></label></div><table id="table_1" class="display responsive nowrap data-t wpDataTable dataTable no-footer" style="" data-described-by="table_1_desc" data-wpdatatable_id="16" role="grid" aria-describedby="table_1_info">
		<thead>
	<tr role="row">
	<td data-class="expand" class="header sort sorting_disabled" style="text-align: center;" rowspan="1" colspan="1">Вид акций</td>
	<td class="header sort numdata integer sorting_disabled" style="text-align: center;" rowspan="1" colspan="1">Количество </td>
	<td class="header sort sorting_disabled" style="text-align: center;" rowspan="1" colspan="1">Цена, <?php echo $arr['valuta']; ?></td>
	<td class="header sort numdata integer sorting_disabled" style="text-align: center;" rowspan="1" colspan="1">Капитализация, <?php echo $arr['valuta']; ?></td></tr>
		</thead>
		<tbody>



					<tr id="table_16_row_0" role="row" class="odd">
						<td>Обыкновенные</td>
									<td class="numdata integer"><?php echo @number_format($akcii,0, '.', ' '); ?></td>
									<td><?php echo number_format((float)$akcii_price,$N_akcii_price, '.', ' '); ?></td>
									<td class="numdata integer"><?PHP echo @number_format((float)$kap,$N_kap, '.', ' '); ?></td>
							</tr><tr id="table_16_row_1" role="row" class="even">
						<td>Привилегированные</td>
									<td class="numdata integer"><?php echo @number_format((float)$akcii_pref,0, '.', ' '); ?></td>
									<td><?php echo number_format((float)$akcii_pref_price,$N_akcii_pref_price, '.', ' '); ?></td>
									<td class="numdata integer"><?PHP echo @number_format((float)$kap_pref,$N_kap_pref, '.', ' ');?></td>
							</tr><tr id="table_16_row_2" role="row" class="odd">
						<td></td>
									<td class="numdata integer"></td>
									<td></td>
									<td class="numdata integer"><?php echo @number_format((float)$sum,$N_sum, '.', ' '); ?></td>
							</tr></tbody>

	</table></div>
	</div>
			</div>
		</div>
	</div></div></div></div></div></div></div>


	<?php	return ob_get_clean();
	}


	function chatbro($arr) {
	ob_start();

if (!isset($arr['number'])) {$arr['number'] = "chat1";}
?>
<h3><?PHP echo $arr['title']; ?></h3>
<div id="chat_<?php echo $arr['number']; ?>"></div>
<script type="text/javascript">
	  function ChatbroLoader(chats, async) {
		async = async || true;
		var params = {
		  embedChatsParameters: chats instanceof Array ? chats : [chats],
		  needLoadCode: typeof Chatbro === "undefined"
		};
		var xhr = new XMLHttpRequest();
		xhr.withCredentials = true;
		xhr.onload = function() {
		  eval(xhr.responseText);
		};
		xhr.onerror = function() {
		  console.error("Chatbro loading error");
		};
		xhr.open("POST", "http://www.chatbro.com/embed_chats", async);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.send("parameters=" + encodeURIComponent(JSON.stringify(params)));
	  }
	  localStorage.clear();

ChatbroLoader({
  chatPath: '<?php echo $arr['path']; ?>',
  containerDivId: 'chat_<?php echo $arr['number']; ?>',
  allowMoveChat: false,
  chatHeight: '400',
  allowMinimizeChat: false,
  chatHeaderBackgroundColor: 'black',
  chatHeaderTextColor: 'white',
  chatBodyBackgroundColor: 'grey',
  chatBodyTextColor: 'lightgrey',
  chatInputBackgroundColor: 'black',
  chatInputTextColor: 'lightgrey',
  showHelpMessages: false
});
</script>
	<?php	return ob_get_clean();
	}

	add_shortcode( 'TBL_div', 'tbl_div' );

	add_shortcode( 'CHATbro', 'chatbro' );
