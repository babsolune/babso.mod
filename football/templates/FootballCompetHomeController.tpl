<section id="module-football" class="category-{CATEGORY_ID} single-item">
	# INCLUDE MENU #
    <h2>{@football.infos}</h2>
	<div class="sub-section">
		<div class="content-container">
			# IF NOT C_VISIBLE #
				<div class="content">
					# INCLUDE NOT_VISIBLE_MESSAGE #
				</div>
			# ENDIF #
			<article itemscope="itemscope" itemtype="https://schema.org/CreativeWork" id="football-item-{ID}" class="football-item# IF C_NEW_CONTENT # new-content# ENDIF #">
				<div class="content">
					# IF C_CHAMPIONSHIP #<div itemprop="text">championship</div># ENDIF #
					# IF C_CUP #<div itemprop="text">cup</div># ENDIF #
					# IF C_TOURNAMENT #
                        <div itemprop="text"># INCLUDE ROUNDS_CALENDAR #</div>
                        # IF C_HAS_MATCHES #<div itemprop="text"># INCLUDE JS_DOC #</div># ENDIF #
                    # ENDIF #
				</div>

				<aside>${ContentSharingActionsMenuService::display()}</aside>

				# IF C_SOURCES #
					<aside class="sources-container">
						<span class="text-strong"><i class="fa fa-map-signs" aria-hidden="true"></i> {@common.sources}</span> :
						# START sources #
							<a itemprop="isBasedOnUrl" href="{sources.URL}" class="pinned link-color offload" rel="nofollow">{sources.NAME}</a># IF sources.C_SEPARATOR ## ENDIF #
						# END sources #
					</aside>
				# ENDIF #
			</article>
		</div>
	</div>
	<footer>
		<meta itemprop="url" content="{U_COMPET}">
		<meta itemprop="description" content="${escape(SUMMARY)}" />
	</footer>
</section>
