<?php
namespace ThemeCheck;
?>
		<footer id="footer">
			<div class="container_footer">
				<div class="footer_first_part">
					<div class="container_logo">
						<img src="<?php echo TC_HTTPDOMAIN;?>/img/images/footer/logo_themeCheck.png" class="logo_themeCheck"/>
					</div>
					<div class="line_footer">
						<img src="<?php echo TC_HTTPDOMAIN;?>/img/images/footer/line_footer.png"/>
					</div>
					<div class="text_footer">
						<p><?php echo __('Themecheck.org is an unofficial fork of the famous Theme Check plugin. It is independant of wordpress.org and joomla.org.'); ?></p>
					</div>
					<div class="container_cms">
						<ul>
							<li>
								<a href="http://wordpress.org" rel="nofollow" target="_blank"><img src="<?php echo TC_HTTPDOMAIN;?>/img/images/footer/wordpress.png" class="wordpress"/></a>
							</li>
							<li>
								<a href="http://www.joomla.org" rel="nofollow" target="_blank"><img src="<?php echo TC_HTTPDOMAIN;?>/img/images/footer/joomla.png" class="joomla"/></a>
							</li>
							<li>
								<a href="http://owasp.org" rel="nofollow" target="_blank"><img src="<?php echo TC_HTTPDOMAIN;?>/img/images/footer/owasp.png" class="owasp"/></a>
							</li>
							<li>
								<a href="https://github.com/themecheck/themecheck" target="_blank"><img src="<?php echo TC_HTTPDOMAIN;?>/img/images/footer/github.png" class="github"/></a>
							</li>
						</ul>
					</div>
				</div>
				<div class="footer_second_part">
					<div class="content_footer_second_part">
						<span class="copyright"><a href="http://www.peoleo.com/peotechnics"><?php echo __("PEOLEO");?></a> &copy; <?php echo date("Y").' '.__("Licensed under the GNU General Public License v2.");?></span>
                                                <span class="langues">
                                                <?php 
						if (!empty($controller->samepage_i18n[I18N::getCurLang()])){
							$langs="";
							foreach ($controller->samepage_i18n as $l=>$url) {
								if ($l == I18N::getCurLang()) $langs .= '&nbsp;<span class="lang_selected">'.strtoupper($l).'</span>&nbsp; | ';
								else $langs .= '<a rel="alternate" hreflang="'.$l.'" href="'.$url.'" >'.strtoupper($l).'</a> | '; 
							}
							echo trim($langs,' |');
						}?>
                                                </span>
						<span class="like_facebook">
							<div class="fb-like" data-href="http://themecheck.org/" data-width="200" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
						</span>
						<span class="copyright_hidden hidden"><a href="http://www.peoleo.com/peotechnics"><?php echo __("PEOLEO");?></a> &copy; <?php echo date("Y").' '.__("Licensed under the GNU General Public License v2.");?></span>
					</div>
				</div>
			</div>
		</footer>
	</div>
		<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/vendor/bootstrap.min.js"></script>
		<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/vendor/bootstrap-filestyle.min.js"></script>
		<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/plugins.js"></script>
		<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/popup.js"></script>

		<!-- JS NEW INTEGRATION -->
		<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/jquery/jquery-1.11.1.min.js"></script>
		<script src="<?php echo TC_HTTPDOMAIN;?>/scripts/Main-dist.js"></script>
		

	</body>
</html>