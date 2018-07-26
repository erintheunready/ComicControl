<? //footer.php - outputs footer for backend pages 

//include javascript for managing general jquery on the page
?>	
	<script>
		$('#sidebar-menu .dropdown a').on('click',function(e){
			$(this).parent().children('ul').slideToggle();
			$(this).find('.angle').toggleClass('fa-angle-right fa-angle-down');
		});
		$('#menu-expand').click( function(){
			$('#sidebar-menu').slideToggle();
		});
		$( window ).resize(function() {
			if($( window ).width() > 800 && $('#sidebar-menu').css("display") == "none") $('#sidebar-menu').css("display", "block");
		});
		$('.tooltip').hover(function(){
			$(this).find('.tooltip-help').css("display","block");
			$(this).find('.tooltip-help').animate({top:'60px',opacity:1}, 200);
		},function(){
			$(this).find('.tooltip-help').animate({top:'70px',opacity:0}, 200, function(){
				$(this).css("display","none");
			});
		});	
	</script>
</body>
</html>