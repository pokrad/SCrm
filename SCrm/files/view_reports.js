$(document).ready(function(){
	$('#report_period_select').on('change', function(e) {
		var id = $(this).val();
		$('select[name=report_period_select]').val(id);
		$('#load_filters').submit();
	});
});