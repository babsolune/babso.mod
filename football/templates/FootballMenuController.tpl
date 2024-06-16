<header class="section-header">
    <div class="align-right">{HEADER_TYPE} - {HEADER_CATEGORY}</div>
    <h1>{HEADER_DIVISION} - {HEADER_SEASON}</h1>
</header>
<div class="compet-menu flex-between controls">
    <nav class="cssmenu cssmenu-horizontal">
        <ul>
            <li><a href="{U_HOME}" class="offload cssmenu-title" aria-label="{@football.menu.infos}"><i class="fa fa-fw fa-house-flag"></i></a></li>
            # IF C_CUP #
                <!--<li><a href="{U_ROUND_CALENDAR}" class="offload cssmenu-title" aria-label="{@football.menu.groups.rounds}"><i class="far fa-fw fa-calendar-days" aria-hidden="true"></i></a></li>-->
                <li><a href="{U_ROUND_BRACKETS}" class="offload cssmenu-title" aria-label="{@football.menu.bracket}"><i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i></a></li>
            # ENDIF #
            # IF C_TOURNAMENT #
                <!--# IF C_ONE_DAY #<li><a href="{U_ROUND_CALENDAR}" class="offload cssmenu-title" aria-label="{@football.menu.groups.rounds}"><i class="far fa-fw fa-calendar-days" aria-hidden="true"></i></a></li># ENDIF #-->
                <li><a href="{U_ROUND_GROUPS}" class="offload cssmenu-title" aria-label="{@football.menu.groups.rounds}"><i class="fa fa-fw fa-list" aria-hidden="true"></i></a></li>
                <li><a href="{U_ROUND_BRACKETS}" class="offload cssmenu-title" aria-label="{@football.menu.brackets.rounds}"><i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i></a></li>
            # ELSE #
                # IF C_CHAMPIONSHIP #
                    <li><a href="{U_DAYS_CALENDAR}" class="offload cssmenu-title" aria-label="{@football.menu.calendar}"><i class="far fa-fw fa-calendar-days" aria-hidden="true"></i></a></li>
                    <li><a href="{U_DAYS_RANKING}" class="offload cssmenu-title" aria-label="{@football.menu.ranking}"><i class="fa fa-fw fa-ranking-star" aria-hidden="true"></i></a></li>
                # ENDIF #
            # ENDIF #
        </ul>
    </nav>
    # IF C_CONTROLS #
        <nav class="cssmenu cssmenu-horizontal">
            <ul>
                <li><a href="{U_EDIT_TEAMS}" class="offload cssmenu-title" aria-label="{@football.menu.teams}"><i class="fa fa-fw fa-people-group" aria-hidden="true"></i></a></li>
                # IF C_HAS_TEAMS #<li><a href="{U_EDIT_PARAMS}" class="offload cssmenu-title" aria-label="{@football.menu.params}"><i class="fa fa-fw fa-cogs" aria-hidden="true"></i></a></li># ENDIF #

                # IF C_CHAMPIONSHIP #
                    # IF C_HAS_DAYS #
                        <li><a href="{U_EDIT_DAYS}" class="offload cssmenu-title" aria-label="{@football.menu.days}"><i class="fa fa-fw fa-users-viewfinder" aria-hidden="true"></i></a></li>
                        # IF C_HAS_MATCHES #
                            <li# IF C_EDIT_DAYS_MATCHES # class="current"# ENDIF #><a href="{U_EDIT_DAYS_MATCHES}" class="offload cssmenu-title" aria-label="{@football.menu.matches}"><i class="fa fa-fw fa-list" aria-hidden="true"></i></a></li>
                        # ENDIF #
                    # ENDIF #
                # ENDIF #
                # IF C_CUP #
                    # IF C_HAS_ROUNDS #
                        <li><a href="{U_EDIT_BRACKET}" class="offload cssmenu-title" aria-label="{@football.menu.bracket}"><i class="fa fa-fw fa-users-viewfinder" aria-hidden="true"></i></a></li>
                        # IF C_HAS_MATCHES #
                            <li# IF C_EDIT_BRACKETS_MATCHES # class="current"# ENDIF #><a href="{U_EDIT_BRACKET_MATCHES}" class="offload cssmenu-title" aria-label="{@football.menu.matches}"><i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i></a></li>
                        # ENDIF #
                    # ENDIF #
                # ENDIF #
                # IF C_TOURNAMENT #
                    # IF C_HAS_GROUPS #
                        <li><a href="{U_EDIT_GROUPS}" class="offload cssmenu-title" aria-label="{@football.menu.groups}"><i class="fa fa-fw fa-users-viewfinder" aria-hidden="true"></i></a></li>
                        # IF C_HAS_MATCHES #
                            <li# IF C_EDIT_GROUPS_MATCHES # class="current"# ENDIF #><a href="{U_EDIT_GROUPS_MATCHES}" class="offload cssmenu-title" aria-label="{@football.menu.groups.matches}"><i class="fa fa-fw fa-list" aria-hidden="true"></i></a></li>
                            <li# IF C_EDIT_BRACKETS_MATCHES # class="current"# ENDIF #><a href="{U_EDIT_BRACKET_MATCHES}" class="offload cssmenu-title" aria-label="{@football.menu.bracket.matches}"><i class="fa fa-fw fa-sitemap fa-rotate-270" aria-hidden="true"></i></a></li>
                        # ENDIF #
                    # ENDIF #
                # ENDIF #
            </ul>
        </nav>
    # ENDIF #
</div>
# IF C_CONTROLS #
    <div class="compet-menu flex-between controls">
        <div></div>
        # IF C_EDIT_DAYS_MATCHES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START days #
                        <li><a href="{days.U_DAY}" aria-label="{days.L_TYPE} {days.NUMBER}" class="roundmenu-title"><span>{days.NUMBER}</span></a></li>
                    # END days #
                </ul>
            </nav>
        # ENDIF #
        # IF C_EDIT_GROUPS_MATCHES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START groups #
                        <li><a href="{groups.U_GROUP}" aria-label="{groups.L_TYPE} {groups.NUMBER}" class="roundmenu-title"><span>{groups.NUMBER}</span></a></li>
                    # END groups #
                </ul>
            </nav>
        # ENDIF #
        # IF C_GROUPS_MATCHES #
            <nav class="roundmenu roundmenu-horizontal">
                <ul>
                    # START groups #
                        <li><a href="{groups.U_GROUP}" aria-label="{groups.L_TYPE} {groups.NUMBER}" class="roundmenu-title"><span>{groups.NUMBER}</span></a></li>
                    # END groups #
                </ul>
            </nav>
        # ENDIF #
        # IF C_EDIT_BRACKETS_MATCHES #
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


<script src="{PATH_TO_ROOT}/football/templates/js/football.highlight# IF C_CSS_CACHE_ENABLED #.min# ENDIF #.js"></script>