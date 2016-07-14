<script type="text/javascript">
Highcharts.theme = {
   colors: ["#7cb5ec", "#f7a35c", "#90ee7e", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
      "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
   chart: {
      backgroundColor: null,
      style: {
         fontFamily: "sans-serif"
      }
   },
   title: {
      style: {
         fontSize: '16px',
         textTransform: 'uppercase',
		 color: '#555',
      }
   },
   tooltip: {
      borderWidth: 0,
      backgroundColor: 'rgba(219,219,216,1)',
      shadow: false
   },
   legend: {
      itemStyle: {
         fontWeight: 'bold',
         fontSize: '13px'
      }
   },
   xAxis: {
      gridLineWidth: 1,
      labels: {
         style: {
            fontSize: '12px',
			color: '#777',
         }
      }
   },
   yAxis: {
      minorTickInterval: 'auto',
      title: {
         style: {
            textTransform: 'uppercase'
         }
      },
      labels: {
         style: {
            fontSize: '12px',
         }
      }
   },
   plotOptions: {
      candlestick: {
         lineColor: '#404048'
      }
   },
   background2: '#F0F0EA'
   
};
Highcharts.setOptions(Highcharts.theme);
</script>
<div id="user-money-dom" class="common">
	<div class="head">
		<div class="name icon-chart-pie">盈亏报表</div>
		<form action="/user/money_search" class="search" container="#user-money-dom .body" data-ispage="true" target="ajax" func="form_submit">
			<div class="timer">
				<input type="text" autocomplete="off" name="fromTime" value="<?php echo date('Y-m-d H:i', $this->request_time_from);?>" id="datetimepicker_fromTime" class="timer">
				<span class="icon icon-calendar"></span>
			</div>
			<div class="sep icon-exchange"></div>
			<div class="timer">
				<input type="text" autocomplete="off" name="toTime" value="<?php echo date('Y-m-d H:i', $this->request_time_to);?>" id="datetimepicker_toTime" class="timer">
				<span class="icon icon-calendar"></span>
			</div>
			<button type="submit" class="btn btn-brown icon-search">查询</button>
		</form>
	</div>
	<div class="body"><?php require(TPL.'/user/money_body.tpl.php');?></div>
</div>
<script type="text/javascript">
$(function() {
	$('#home').removeClass('on');
	$('#user-money').addClass('on');
	// 时间选择插件
	$('#datetimepicker_fromTime,#datetimepicker_toTime').datetimepicker(datetimepicker_opt);
});
</script>