<header class="section-header">
    <div class="align-right">
        <a href="{U_HOME}" class="offload" aria-label="{@common.home}"><i class="fa fa-fw fa-house"></i></a>
        {HEADER_TYPE} - <a href="{U_HEADER_CATEGORY}" class="offload">{HEADER_CATEGORY}</a>
    </div>
    # IF C_IS_SUB #
        <h1>{HEADER_MASTER_DIVISION} {HEADER_MASTER_SEASON}</h1>
        <h2>{HEADER_DIVISION}</h2>
    # ELSE #
        <h1>{HEADER_DIVISION} {HEADER_SEASON}</h1>
    # ENDIF #
    <div class="flex-between flex-between-large">
        # IF C_SOURCES #
            <aside class="sources-container">
                <span aria-label="{@common.sources}"><i class="fa fa-map-signs" aria-hidden="true"></i> </span> :
                # START sources #
                    <a itemprop="isBasedOnUrl" href="{sources.URL}" class="pinned link-color offload" target="_blank" rel="noopener noreferrer nofollow">{sources.NAME}</a># IF sources.C_SEPARATOR # | # ENDIF #
                # END sources #
            </aside>
        # ENDIF #
        # IF IS_MODERATOR ## INCLUDE EVENT_LIST ## ENDIF #
    </div>
    <div class="spacer"></div>
</header>
<div class="event-menu flex-between controls">
    <nav class="cssmenu cssmenu-horizontal bgc-sub align-center">
        <ul>
            # IF C_IS_MASTER #
                # START sub_events #
                    <li><a class="offload cssmenu-title" href="{sub_events.U_EVENT}"># IF NOT IS_MOBILE_DEVICE #<i class="fa fa-fw fa-house"></i># ENDIF #<span class="small d-block">{sub_events.DIVISION_NAME}</span></a></li>
                # END sub_events #
            # ELSE #
                # IF C_IS_SUB #<li><a href="{U_EVENT_MASTER}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-house-flag"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{HEADER_MASTER_DIVISION}</span></a></li># ENDIF #
                <li><a href="{U_EVENT_HOME}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-info"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.infos}</span></a></li>
                # IF C_CUP #
                    <li><a href="{U_ROUND_BRACKETS}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.single.bracket}</span></a></li>
                # ENDIF #
                # IF C_TOURNAMENT #
                    <li><a href="{U_ROUND_GROUPS}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-list" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.rounds.groups}</span></a></li>
                    <li>
                        <a href="{U_ROUND_BRACKETS}" class="offload cssmenu-title align-center">
                            # IF C_FINALS_RANKING #
                                <span class="stacked">
                                    <i class="fa fa-fw fa-list" aria-hidden="true"></i>
                                    <i class="fa fa-trophy notice stack-event stack-top-right" aria-hidden="true"></i>
                                </span><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.rounds.brackets}</span>
                            # ELSE #
                                <i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.rounds.brackets}</span>
                            # ENDIF #
                        </a>
                    </li>
                # ENDIF #
                # IF C_CHAMPIONSHIP #
                    <li><a href="{U_DAYS_CALENDAR}" class="offload cssmenu-title align-center"><i class="far fa-fw fa-calendar-days" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.calendar}</span></a></li>
                    <li><a href="{U_DAYS_RANKING}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-ranking-star" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.ranking}</span></a></li>
                # ENDIF #
            # ENDIF #
        </ul>
    </nav>
    # IF NOT C_IS_MASTER #
        # IF C_CONTROLS #
            <nav class="bgc moderator cssmenu cssmenu-horizontal align-center">
                <ul>
                    <li><a href="{U_EDIT_TEAMS}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-people-group" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.teams}</span></a></li>
                    # IF C_HAS_TEAMS #<li><a href="{U_EDIT_PARAMS}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-cogs" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.params}</span></a></li># ENDIF #

                    # IF C_CHAMPIONSHIP #
                        # IF C_HAS_DAYS #
                            <li><a href="{U_EDIT_DAYS}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-users-viewfinder" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.create.games}</span></a></li>
                            # IF C_HAS_GAMES #
                                <li# IF C_EDIT_DAYS_GAMES # class="current"# ENDIF #><a href="{U_EDIT_DAYS_GAMES}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-list" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.games}</span></a></li>
                            # ENDIF #
                        # ENDIF #
                    # ENDIF #
                    # IF C_CUP #
                        # IF C_HAS_ROUNDS #
                            <li><a href="{U_EDIT_BRACKET}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-users-viewfinder" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.create.games}</span></a></li>
                            # IF C_HAS_GAMES #
                                <li# IF C_EDIT_BRACKETS_GAMES # class="current"# ENDIF #><a href="{U_EDIT_BRACKET_GAMES}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.games}</span></a></li>
                            # ENDIF #
                        # ENDIF #
                    # ENDIF #
                    # IF C_TOURNAMENT #
                        # IF C_HAS_GROUPS #
                            <li><a href="{U_EDIT_GROUPS}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-users-viewfinder" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.create.games}</span></a></li>
                            # IF C_HAS_GAMES #
                                <li# IF C_EDIT_GROUPS_GAMES # class="current"# ENDIF #><a href="{U_EDIT_GROUPS_GAMES}" class="offload cssmenu-title align-center"><i class="fa fa-fw fa-list" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.games.groups}</span></a></li>
                                <li# IF C_EDIT_BRACKETS_GAMES # class="current"# ENDIF #>
                                    <a href="{U_EDIT_BRACKET_GAMES}" class="offload cssmenu-title align-center">
                                        # IF C_FINALS_RANKING #
                                            <span class="stacked">
                                                <i class="fa fa-fw fa-list" aria-hidden="true"></i>
                                                <i class="fa fa-trophy notice stack-event stack-top-right" aria-hidden="true"></i>
                                            </span><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.games.bracket}</span>
                                        # ELSE #
                                            <i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i><span class="small d-block# IF IS_MOBILE_DEVICE # sr-only# ENDIF #">{@scm.menu.config.games.bracket}</span>
                                        # ENDIF #
                                    </a>
                                </li>
                            # ENDIF #
                        # ENDIF #
                    # ENDIF #
                </ul>
            </nav>
        # ENDIF #
    # ENDIF #
</div>
# IF C_CONTROLS #
    <div class="event-menu flex-between controls">
        <div></div>
        # IF C_EDIT_DAYS_GAMES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START days #
                        <li><a href="{days.U_DAY}" aria-label="{days.L_TYPE} {days.NUMBER}" class="roundmenu-title"><span>{days.NUMBER}</span></a></li>
                    # END days #
                    <li><a href="{U_CHECK_DAYS}" aria-label="{@scm.check.days}" class="roundmenu-title"><span><i class="fa fa-calendar-check warning"></i></span></a></li>
                </ul>
            </nav>
        # ENDIF #
        # IF C_DAYS_GAMES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START days #
                        <li><a href="{days.U_DAY}" aria-label="{days.L_TYPE} {days.NUMBER}" class="roundmenu-title"><span>{days.NUMBER}</span></a></li>
                    # END days #
                </ul>
            </nav>
        # ENDIF #
        # IF C_EDIT_GROUPS_GAMES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START groups #
                        <li><a href="{groups.U_GROUP}" aria-label="{groups.L_TYPE} {groups.NUMBER}" class="roundmenu-title"><span>{groups.NUMBER}</span></a></li>
                    # END groups #
                </ul>
            </nav>
        # ENDIF #
        # IF C_GROUPS_GAMES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START groups #
                        <li><a href="{groups.U_GROUP}" aria-label="{groups.L_TYPE} {groups.NUMBER}" class="roundmenu-title"><span>{groups.NUMBER}</span></a></li>
                    # END groups #
                </ul>
            </nav>
        # ENDIF #
        # IF C_EDIT_BRACKETS_GAMES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START bracket #
                        <li><a href="{bracket.U_BRACKET}" class="roundmenu-title"><span>{bracket.BRACKET_ROUND}</span></a></li>
                    # END bracket #
                </ul>
            </nav>
        # ENDIF #
    </div>
# ENDIF #
<script src="{PATH_TO_ROOT}/scm/templates/js/scm.highlight# IF C_CSS_CACHE_ENABLED #.min# ENDIF #.js"></script>
# IF IS_MODERATOR #
    <script>
        const selectElement = document.getElementById('ScmMenuService_event_list');
        selectElement.addEventListener('change', (event) => {
            const event_id = event.target.value;
            window.location.href = '{PATH_TO_ROOT}/scm/' + event_id + '-redirect/informations';
        });
    </script>
# ENDIF #