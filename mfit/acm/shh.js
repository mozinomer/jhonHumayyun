
$('.acm-slider').slick({
  autoplay:true,
  autoplaySpeed:3500,
  arrows:true,
  centerMode:false,
  slidesToShow:1,
  slidesToScroll:1
}); 
$('.news-blog-slider').slick({
  autoplay:true,
  autoplaySpeed:3500,
  arrows:true,
  centerMode:false,
  slidesToShow:3,
  slidesToScroll:1,
  responsive: [
  {
    breakpoint: 1024,
    settings: {
      slidesToShow: 1,
      slidesToScroll: 1,
      infinite: false,
      dots: true,
      arrows: false
    }
  },
  ]
});   