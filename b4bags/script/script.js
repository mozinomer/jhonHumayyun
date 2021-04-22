$(document).ready(function () {

	$('ul.tabs li').click(function () {
		var tab_id = $(this).attr('data-tab');

		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		// setTimeout(function(){ 
			$("#" + tab_id).addClass('current');
		// }, 2000);
		
	})

})
$('.bag-slider').slick({
	slidesToShow: 1,
	arrows: true,
	dots: false,
	prevArrow: '<i class="fa fa-angle-left" aria-hidden="true"></i>',
	nextArrow: '<i class="fa fa-angle-right" aria-hidden="true"></i>',
	loop: false,
	

});
$('.thumbail-slider1').owlCarousel({
	loop: true,
	margin: 10,
	nav: false,
	dots: false,
	thumbs: true,
	thumbImage: true,
	thumbsPrerendered: true,
	thumbContainerClass: 'owl-thumbs',
	thumbItemClass: 'owl-thumb-item',
	responsive: {
		0: {
			items: 1
		},
		600: {
			items: 1
		},
		1000: {
			items: 1
		}
	}
})

$(document).ready(function () {
	$('.minus').click(function () {
		var $input = $(this).parent().find('input');
		var count = parseInt($input.val()) - 1;
		count = count < 1 ? 1 : count;
		$input.val(count);
		$input.change();
		return false;
	});
	$('.plus').click(function () {
		var $input = $(this).parent().find('input');
		$input.val(parseInt($input.val()) + 1);
		$input.change();
		return false;
	});
});
