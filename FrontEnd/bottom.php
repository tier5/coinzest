</div> <!-- wrapper -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
<!--	<script src="FrontEnd/js/jquery.min.js"></script>-->
<!--	<script src="FrontEnd/js/bootstrap.min.js"></script>-->
	<script src="FrontEnd/js/jquery.mb.YTPlayer.js"></script>
	
	<script>
	$(document).ready( function() {
    $('#myCarousel').carousel({
    	interval:  4000
	});
	
	var clickEvent = false;
	$('#myCarousel').on('click', '.nav a', function() {
			clickEvent = true;
			$('.nav li').removeClass('active');
			$(this).parent().addClass('active');		
	}).on('slide.bs.carousel', function(e) {
		if(!clickEvent) {
			var count = $('#nav-carousel').children().length -1;
			var current = $('#nav-carousel li.active');
			current.removeClass('active').next().addClass('active');
			var id = parseInt(current.data('slide-to'));
			if(count == id) {
				$('#nav-carousel li').first().addClass('active');	
			}
		}
		clickEvent = false;
	});
});
	</script>
	<script>
		$(document).ready(function () {
			$(".player").mb_YTPlayer();
		});
	</script>
	
	<!-- SWITCHER -->
	
	<script>
		/*-----------------------------------------------------------------------------------
/* Styles Switcher
-----------------------------------------------------------------------------------*/

window.console = window.console || (function(){
	var c = {}; c.log = c.warn = c.debug = c.info = c.error = c.time = c.dir = c.profile = c.clear = c.exception = c.trace = c.assert = function(){};
	return c;
})();


jQuery(document).ready(function(jQuery) {
	
        // Style Switcher	
		jQuery('#style-switcher').animate({
			right: '-300px'
		});
		
		jQuery('#style-switcher h2 a').click(function(e){
			e.preventDefault();
			var div = jQuery('#style-switcher');
			console.log(div.css('right'));
			if (div.css('right') === '-300px') {
				jQuery('#style-switcher').animate({
					right: '0px'
				}); 
			} else {
				jQuery('#style-switcher').animate({
					right: '-300px'
				});
			}
		})
                
		// Color Changer
		
		
		
		
		jQuery(".s2" ).click(function(){
			jQuery("#colors" ).attr("href", "FrontEnd/assets/css/themes/flat-brown-dark-theme.css" );
			return false;
		});
		
		jQuery(".s3" ).click(function(){
			jQuery("#colors" ).attr("href", "FrontEnd/assets/css/themes/droid-dark-theme.css" );
			return false;
		});
		jQuery(".s4" ).click(function(){
			jQuery("#colors" ).attr("href", "FrontEnd/assets/css/themes/glossy-blured-glass-theme.css" );
			return false;
		});
		
		jQuery(".s5" ).click(function(){
			jQuery("#colors" ).attr("href", "FrontEnd/assets/css/themes/black-classic-theme.css" );
			return false;
		});
		jQuery(".s6" ).click(function(){
			jQuery("#colors" ).attr("href", "FrontEnd/assets/css/themes/orange-dark-theme.css" );
			return false;
		});
		
		jQuery(".s7" ).click(function(){
			jQuery("#colors" ).attr("href", "FrontEnd/assets/css/themes/blue-print-theme.css" );
			return false;
		});
		
		
		// Layout Switcher
		jQuery(".s8" ).click(function(){
			jQuery("#layout" ).attr("href", "css/wide.html" );
			return false;
		});

		jQuery("#layout-switcher").on('change', function() {
			jQuery('#layout').attr('href', jQuery(this).val() + '.css');
		});;

		
		
		
		jQuery('.colors li a').click(function(e){
			e.preventDefault();
			jQuery(this).parent().parent().find('a').removeClass('active');
			jQuery(this).addClass('active');
		})
		
	
		jQuery('.bg li a').click(function(e){
			e.preventDefault();
			jQuery(this).parent().parent().find('a').removeClass('active');
			jQuery(this).addClass('active');
			var bg = jQuery(this).css('backgroundImage');
			jQuery('body').css('backgroundImage',bg)
		})
                
		
		jQuery('.bgsolid li a').click(function(e){
			e.preventDefault();
			jQuery(this).parent().parent().find('a').removeClass('active');
			jQuery(this).addClass('active');
			var bg = jQuery(this).css('backgroundColor');
			jQuery('body').css('backgroundColor',bg).css('backgroundImage','none')
		})
                
		jQuery('.navcolor li a').click(function(e){
			e.preventDefault();
			jQuery(this).parent().parent().find('a').removeClass('active');
			jQuery(this).addClass('active');
			var bg = jQuery(this).css('backgroundColor');
			jQuery('#navigation').css('backgroundColor',bg).css('backgroundImage','none');
			jQuery('#navigation ul ul').css('backgroundColor',bg).css('backgroundImage','none');
                        
		})
		
		
		jQuery('#reset a').click(function(e){
			
                        jQuery('#navigation').css('backgroundColor','#333');
			jQuery('#navigation ul ul').css('backgroundColor','#333');
                        jQuery("#colors" ).attr("href", "FrontEnd/assets/css/themes/flat-cian-theme.css" );
		})
			

	});
	</script>

</body>
</html>
