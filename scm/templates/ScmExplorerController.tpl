<section id="module-scm" class="several-items">
	<header class="section-header">
		<div class="controls align-right">
			<a class="offload" href="${relative_url(SyndicationUrlBuilder::rss('scm', CATEGORY_ID))}" aria-label="{@common.syndication}"><i class="fa fa-rss warning" aria-hidden="true"></i></a>
			# IF C_CATEGORY ## IF IS_ADMIN #<a class="offload" href="{U_EDIT_CATEGORY}" aria-label="{@common.edit}"><i class="far fa-edit" aria-hidden="true"></i></a># ENDIF ## ENDIF #
		</div>
		<h1 class="flex-between">
			{MODULE_NAME}
		</h1>
	</header>
    <div class="message-helper bgc notice">{@scm.warning.current.season}</div>

	# IF C_NO_ITEM #
		<div class="sub-section">
			<div class="content-container">
				<div class="content">
					<div class="message-helper bgc notice">{@common.no.item.now}</div>
				</div>
			</div>
		</div>
	# ELSE #
        # IF C_CURRENT_GAMES_CONFIG #
            <div class="sub-section">
                <div class="content-container">
                    <article class="content">
                        <header><h2>{@scm.current.games}</h2></header>
                        # IF C_CURRENT_GAMES #
                            <div class="cell-flex cell-columns-4">
                                # START current_games #
                                    <div id="{current_games.GAME_ID}" class="cell game-container">
                                        <div class="small text-italic">
                                            <a href="{current_games.U_EVENT}" class="offload">{current_games.EVENT_NAME}</a>
                                            <span class="d-block">
                                                # IF current_games.C_TYPE_GROUP #{@scm.group} {current_games.GROUP}# ENDIF #
                                                # IF current_games.C_TYPE_BRACKET #{current_games.BRACKET}# ENDIF #
                                                # IF current_games.C_TYPE_DAY #{@scm.day} {current_games.DAY}# ENDIF #
                                            </span>
                                        </div>
                                        <div  class="id-{current_games.HOME_ID} game-team game-home# IF current_games.C_HOME_FAV # text-strong# ENDIF #"
                                                # IF current_games.C_HOME_WIN # style="background-color: {current_games.WIN_COLOR}"# ENDIF #>
                                            <div class="home-{current_games.GAME_ID} home-team">
                                                # IF current_games.HOME_ID #
                                                    <div class="flex-team flex-left">
                                                        # IF current_games.C_HAS_HOME_LOGO #<img src="{current_games.HOME_LOGO}" alt="{current_games.HOME_TEAM}"># ENDIF #
                                                        <span>{current_games.HOME_TEAM}</span>
                                                    </div>
                                                # ENDIF #
                                            </div>
                                            <div class="game-score home-score width-px-50">{current_games.HOME_SCORE}# IF current_games.C_HAS_PEN # <span class="small">({current_games.HOME_PEN})</span># ENDIF #</div>
                                        </div>
                                        <div class="id-{current_games.AWAY_ID} game-team game-away# IF current_games.C_AWAY_FAV # text-strong# ENDIF #"
                                                # IF current_games.C_AWAY_WIN # style="background-color: {current_games.WIN_COLOR}"# ENDIF #>
                                            <div class="away-{current_games.GAME_ID} away-team">
                                                # IF current_games.AWAY_ID #
                                                    <div class="flex-team flex-left">
                                                        # IF current_games.C_HAS_AWAY_LOGO #<img src="{current_games.AWAY_LOGO}" alt="{current_games.AWAY_TEAM}"># ENDIF #
                                                        <span>{current_games.AWAY_TEAM}</span>
                                                    </div>
                                                # ENDIF #
                                            </div>
                                            <div class="game-score away-score width-px-50">{current_games.AWAY_SCORE}# IF current_games.C_HAS_PEN # <span class="small">({current_games.AWAY_PEN})</span># ENDIF #</div>
                                        </div>
                                    </div>
                                # END current_games #
                            </div>
                        # ELSE #
                            <div class="message-helper bgc notice">{@scm.no.current.games}</div>
                        # ENDIF #
                    </article>
                </div>
            </div>
        # ENDIF #

		<div class="sub-section">
			<div class="content-container">
				<article class="scm-item">
					<div class="content">
                        <div class="cell-flex cell-columns-2 cell-between">
                            <div class="width-pc-45">
                                <header><h2>{@scm.mini.next}</h2></header>
                                # IF C_NEXT_ITEMS #
                                    # START next_categories #
                                        <h6 class="text-strong">{next_categories.CATEGORY_NAME}</h6>
                                        # START next_categories.next_items #
                                            <div class="cell cell-tile cell-game">
                                                <div class="game-header bgc-sub flex-between">
                                                    <div>
                                                        # IF next_categories.next_items.C_IS_SUB #<a class="offload smaller" href="{next_categories.next_items.U_MASTER_EVENT}">{next_categories.next_items.MASTER_EVENT}</a> - # ELSE #<span></span># ENDIF #
                                                        <a class="offload" href="{next_categories.next_items.U_EVENT}">{next_categories.next_items.GAME_DIVISION}</a>
                                                    </div>
                                                    # IF next_categories.next_items.C_LATE #<div class="cell-full bgc-sub smaller text-italic" colspan="3">{@scm.game.late}</div># ENDIF #
                                                    <div>{@scm.day.short}{next_categories.next_items.CLUSTER} : {next_categories.next_items.GAME_DATE_DAY}/{next_categories.next_items.GAME_DATE_MONTH}/{next_categories.next_items.YEAR} {next_categories.next_items.GAME_DATE_HOUR}:{next_categories.next_items.GAME_DATE_MINUTE}</div>
                                                </div>
                                                <div class="game-body category-{next_categories.next_items.CATEGORY_ID}# IF next_categories.next_items.C_EXEMPT # bgc notice# ENDIF #">
                                                    <span class="home-team"><a href="{next_categories.next_items.U_HOME_CALENDAR}" aria-label="{@scm.see.club.calendar}" class="offload# IF next_categories.next_items.C_HOME_FAV # text-strong# ENDIF ## IF next_categories.next_items.HOME_FORFEIT # warning# ENDIF #">{next_categories.next_items.HOME_TEAM}</a></span>
                                                    <span class="game-score">-</span>
                                                    <span class="away-team"><a href="{next_categories.next_items.U_AWAY_CALENDAR}" aria-label="{@scm.see.club.calendar}" class="offload# IF next_categories.next_items.C_AWAY_FAV # text-strong# ENDIF ## IF next_categories.next_items.AWAY_FORFEIT # warning# ENDIF #">{next_categories.next_items.AWAY_TEAM}</a></span>
                                                </div>
                                            </div>
                                        # END next_categories.next_items #
                                    # END next_categories #
                                # ELSE #
                                    <div class="message-helper bgc notice">{@common.no.item.now}</div>
                                # ENDIF #
                            </div>
                            <div class="width-pc-45">
                                <header><h2>{@scm.mini.prev}</h2></header>
                                # IF C_PREV_ITEMS #
                                    # START prev_categories #
                                        <h6 class="text-strong">{prev_categories.CATEGORY_NAME}</h6>
                                        # START prev_categories.prev_items #
                                            <div class="cell cell-tile cell-game">
                                                <div class="game-header bgc-sub flex-between">
                                                    <div>
                                                        # IF prev_categories.prev_items.C_IS_SUB #<a class="offload smaller" href="{prev_categories.prev_items.U_MASTER_EVENT}">{prev_categories.prev_items.MASTER_EVENT}</a> - # ELSE #<span></span># ENDIF #
                                                        <a class="offload" href="{prev_categories.prev_items.U_EVENT}">{prev_categories.prev_items.GAME_DIVISION}</a>
                                                    </div>
                                                    # IF prev_categories.prev_items.C_LATE #<div class="cell-full bgc-sub smaller text-italic" colspan="3">{@scm.game.late}</div># ENDIF #
                                                    <div>{@scm.day.short}{prev_categories.prev_items.CLUSTER} : {prev_categories.prev_items.GAME_DATE_DAY}/{prev_categories.prev_items.GAME_DATE_MONTH}/{prev_categories.prev_items.YEAR} {prev_categories.prev_items.GAME_DATE_HOUR}:{prev_categories.prev_items.GAME_DATE_MINUTE}</div>
                                                </div>
                                                <div class="game-body category-{prev_categories.prev_items.CATEGORY_ID}# IF prev_categories.prev_items.C_EXEMPT # bgc notice# ENDIF #">
                                                    <span class="d-inline-block align-right width-pc-35"><a href="{prev_categories.prev_items.U_HOME_CALENDAR}" aria-label="{@scm.see.club.calendar}" class="offload# IF prev_categories.prev_items.C_HOME_FAV # text-strong# ENDIF ## IF prev_categories.prev_items.HOME_FORFEIT # warning# ENDIF #">{prev_categories.prev_items.HOME_TEAM}</a></span>
                                                    <span class="d-inline-block align-center width-pc-20">{prev_categories.prev_items.HOME_SCORE} - {prev_categories.prev_items.AWAY_SCORE}</span>
                                                    <span class="d-inline-block align-left width-pc-35"><a href="{prev_categories.prev_items.U_AWAY_CALENDAR}" aria-label="{@scm.see.club.calendar}" class="offload# IF prev_categories.prev_items.C_AWAY_FAV # text-strong# ENDIF ## IF prev_categories.prev_items.AWAY_FORFEIT # warning# ENDIF #">{prev_categories.prev_items.AWAY_TEAM}</a></span>
                                                </div>
                                            </div>
                                        # END prev_categories.prev_items #
                                # END prev_categories #
                                # ELSE #
                                    <div class="message-helper bgc notice">{@common.no.item.now}</div>
                                # ENDIF #
                            </div>
                        </div>
					</div>
				</article>

			</div>
		</div>
	# ENDIF #
	<footer></footer>
</section>
<script src="{PATH_TO_ROOT}/scm/templates/js/scm.width# IF C_CSS_CACHE_ENABLED #.min# ENDIF #.js"></script>
<script src="{PATH_TO_ROOT}/scm/templates/js/scm.highlight# IF C_CSS_CACHE_ENABLED #.min# ENDIF #.js"></script>
