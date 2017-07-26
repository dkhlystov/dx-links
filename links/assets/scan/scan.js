$(function() {

	$(document).on('click', '.scan-button', scanClick);
	$(document).on('click', '.scan-addition', additionClick);
	$(document).on('click', '.scan-url', scanUrlClick);

	function scanClick(e) {
		$(this).prop('disabled', true);
		$('.scan-progress .progress-bar').addClass('active');

		scanNext();
	};

	function additionClick(e) {
		$(this).closest('tr').find('.scan-addition-block').toggleClass('hidden');
	};

	function scanUrlClick(e) {
		e.preventDefault();

		$.get(this.href, {'_': Date.now()}, function(data) {
			scanSuccessView(data);
		}, 'json');
	};

	function scanNext() {
		var $button = $('.scan-button'),
			$progress = $('.scan-progress .progress-bar');

		$.get($button.data('url'), {'_': Date.now()}, function(data) {
			scanSuccessView(data);

			//progress
			var p = 0;
			if (data.total)
				p = data.ready / data.total * 100;
			$progress.css('width', p + '%');

			if (data.ready < data.total) {
				scanNext();
			} else {
				$button.prop('disabled', false);
				$progress.removeClass('active');
			}
		}, 'json');
	};

	function scanSuccessView(data) {
		var $view = $(data.view);

		//summary
		$('.scan-summary').replaceWith($view.find('.scan-summary'));

		//rows
		var $list = $('.scan-list tbody');
		$view.find('tbody tr').each(function() {
			var $this = $(this),
				$tr = $list.find('tr[data-key="' + $this.data('key') + '"]');

			if ($tr.length) {
				$tr.replaceWith($this);
			} else {
				$list.append($this);
			}
		});
	};

});
