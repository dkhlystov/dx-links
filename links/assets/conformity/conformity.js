$(function() {

	$(document).on('click', 'a.conformity', conformityClick);
	$(document).on('click', '.conformity-cancel', cancelClick);
	$(document).on('click', '.conformity-set', setClick);

	function conformityClick(e) {
		e.preventDefault();

		var $modal = $('.list-modal')
			$tr = $(this).closest('tr'),
			val = '',
			$container = $tr.find('td:eq(1) > div');

		if ($container.length)
			val = $container.data('id');

		$modal.find('select').val(val).trigger('chosen:updated');
		$modal.data('tr', $tr);

		$modal.modal();
	};

	function cancelClick() {
		$(this).closest('.modal').modal('hide');
	};

	function setClick(e) {
		e.preventDefault();

		var $modal = $(this).closest('.modal'),
			$tr = $modal.data('tr'),
			val = $modal.find('select').val();

		if (val === '') {
			alert('Страница не выбрана.');
			return;
		}

		$.get(this.href, {'src_id': $tr.data('key'), 'dest_id': val}, function(data) {
			if (data.success) {
				$tr.find('td:eq(1)').html(data.html);
			}
			console.log(data);
		}, 'json');

		$modal.modal('hide');
	};

	$('.list-modal').find('select').chosen({'width': '100%'});

});
