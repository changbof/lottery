<div id="money-total">
	<div class="block">
		<span class="text green">总收入</span>
		<span class="val"><?php echo $data['income'];?></span>
	</div>
	<div class="block">
		<span class="text red">总支出</span>
		<span class="val"><?php echo $data['expenditure'];?></span>
	</div>
	<div class="block">
		<span class="text blue">总结余</span>
		<span class="val"><?php echo $data['total'];?></span>
	</div>
</div>
<div id="money-data"></div>
<script type="text/javascript">
$(function () {
    var categories = [<?php echo $data['yAxis'];?>];
	$('#money-data').highcharts({
		chart: {
			type: 'bar'
		},
		title: {
			text: '收支明细：<?php echo date('Y-m-d', $this->request_time_from);?> ~ <?php echo date('Y-m-d', $this->request_time_to);?>'
		},
		xAxis: [{
			categories: categories,
			reversed: false,
			labels: {
				step: 1
			}
		}, { // mirror axis on right side
			opposite: true,
			reversed: false,
			categories: categories,
			linkedTo: 0,
			labels: {
				step: 1
			}
		}],
		yAxis: {
			title: {text: null},
		},
		plotOptions: {
			series: {
				stacking: 'normal'
			}
		},
		tooltip: {
			formatter: function () {
				return '<b>[' + this.point.category + ']</b>' + this.series.name + ' ' + Highcharts.numberFormat(this.point.y, 0) + ' 元';
			}
		},
		series: [{
			name: '支出',
			data: [<?php echo $data['series_2'];?>]
		}, {
			name: '收入',
			data: [<?php echo $data['series_1'];?>]
		}]
	});
});
</script>