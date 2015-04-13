<form id='<?php echo $formID; ?>'  method='POST'>
    <input type='hidden' name='step' value='2' />

    <table style="margin-left: 50px;">
        <tr>
            <td>
                <h1><?= $_SESSION['brand']['brandName']; ?> Store Locator</h1>
                <table width="85%">
                    <tr>
                        <td colspan="2">
                            <p><strong>Find a store in your area or search by state</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td>Zip Code:</td>
                        <td><input type="text" name="inp_zipcode" size="5"/></td>
                    </tr>
                    <tr>
                        <td>Search Radius:</td>
                        <td>
                            <select name="inp_searchRadius">
                                <option value="25">25 Miles</option>
                                <option value="50">50 Miles</option>
                                <option value="100" selected>100 Miles</option>
                                <option value="200">200 Miles</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="button" value="FIND"/>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <img src="/css/<?= $_SESSION['brand']['brandLabel']; ?>/images/items/store_locator_map.png"
                     alt="" width="460" height="352" border="0" usemap="#MapStore" class="no-border" />

                <!-- GP STORE LOCATOR MAP -->
                <map name="MapStore" id="MapStore">
                    <AREA title=Alaska shape=POLY
                          alt=Alaska
                          coords=12,69,28,61,42,49,47,48,62,49,74,59,79,64,85,61,83,56,71,41,67,44,62,42,55,5,31,1,16,13,16,18,12,24,15,34,12,44,22,54,28,56
                          href="<?= BRAND_URL; ?>locations/AK">
                    <AREA
                        title=Washington shape=POLY alt=Washington
                        coords=29,71,45,81,49,71,67,75,88,77,85,106,39,108,37,96,31,96
                        href="<?= BRAND_URL; ?>locations/WA"><AREA
                        title=Oregon shape=POLY alt=Oregon
                        coords=31,100,39,104,39,112,85,112,84,118,74,126,74,132,77,132,75,155,66,155,17,145,19,137
                        href="<?= BRAND_URL; ?>locations/OR"><AREA
                        title=Idaho shape=POLY alt=Idaho
                        coords=89,81,88,109,88,115,77,128,79,156,119,156,120,136,113,137,109,134,101,114,93,81
                        href="<?= BRAND_URL; ?>locations/ID"><AREA
                        title=California shape=POLY alt=California
                        coords=18,148,49,156,46,189,83,236,83,255,61,256,33,230,25,197,14,164
                        href="<?= BRAND_URL; ?>locations/CA"><AREA
                        title=Nevada shape=POLY alt=Nevada
                        coords=51,156,48,191,86,238,88,226,92,159,62,159,52,155
                        href="<?= BRAND_URL; ?>locations/NV"><AREA
                        title=Utah shape=POLY alt=Utah
                        coords=94,159,92,216,133,218,136,175,120,173,118,160,92,159
                        href="<?= BRAND_URL; ?>locations/UT"><AREA
                        title=Montana shape=POLY alt=Montana
                        coords=95,78,112,133,117,133,119,128,174,130,174,88,95,80
                        href="<?= BRAND_URL; ?>locations/MT"><AREA
                        title=Arizona shape=POLY alt=Arizona
                        coords=92,222,84,260,111,282,130,282,134,220
                        href="<?= BRAND_URL; ?>locations/AZ"><AREA
                        title=Hawaii shape=POLY alt=Hawaii
                        coords=7,255,18,250,45,262,65,274,87,303,67,315,61,296,59,284,29,292
                        href="<?= BRAND_URL; ?>locations/HI"><AREA
                        title=Colorado shape=POLY alt=Colorado
                        coords=139,174,136,218,192,217,192,177
                        href="<?= BRAND_URL; ?>locations/CO"><AREA
                        title="New Mexico" shape=POLY alt="New Mexico"
                        coords=135,222,185,222,187,271,149,271,149,275,133,281
                        href="<?= BRAND_URL; ?>locations/NM"><AREA
                        title="North Dakota" shape=POLY
                        alt="North Dakota" coords=177,85,178,117,234,118,231,85
                        href="<?= BRAND_URL; ?>locations/ND"><AREA
                        title="South Dakota" shape=POLY
                        alt="South Dakota"
                        coords=178,119,178,152,222,152,236,154,236,129,232,123,232,119
                        href="<?= BRAND_URL; ?>locations/SD"><AREA
                        title=Nebraska shape=POLY alt=Nebraska
                        coords=179,155,179,175,195,177,195,185,246,183,236,155,220,155
                        href="<?= BRAND_URL; ?>locations/NE"><AREA
                        title=Kansas shape=POLY alt=Kansas
                        coords=195,186,196,216,256,218,257,200,248,186,196,186
                        href="<?= BRAND_URL; ?>locations/KS"><AREA
                        title=Oklahoma shape=POLY alt=Oklahoma
                        coords=188,220,188,226,212,228,212,248,234,255,260,255,258,234,256,220
                        href="<?= BRAND_URL; ?>locations/OK"><AREA
                        title=Texas shape=POLY alt=Texas
                        coords=188,228,187,274,153,275,165,289,169,292,175,308,181,310,185,306,189,298,198,301,207,308,214,324,219,330,222,337,238,345,240,339,236,320,254,310,260,300,265,300,268,294,270,290,269,282,266,275,262,271,261,259,248,257,232,257,210,249,210,230
                        href="<?= BRAND_URL; ?>locations/TX"><AREA
                        title=Minnesota shape=POLY alt=Minnesota
                        coords=233,85,235,122,239,129,239,141,279,141,273,137,260,129,261,115,269,99,280,90,272,94,269,90,257,86
                        href="<?= BRAND_URL; ?>locations/MN"><AREA
                        title=Iowa shape=POLY alt=Iowa
                        coords=236,144,240,155,246,174,278,174,282,174,282,164,288,160,281,152,278,145,236,145
                        href="<?= BRAND_URL; ?>locations/IA"><AREA
                        title=Missouri shape=POLY alt=Missouri
                        coords=248,177,255,187,260,201,260,220,296,220,298,226,303,210,291,196,291,192,286,192,279,182,282,178,278,178
                        href="<?= BRAND_URL; ?>locations/MO"><AREA
                        title=Arkansas shape=POLY alt=Arkansas
                        coords=257,223,261,235,261,249,264,257,264,261,288,261,287,251,295,235,298,227,294,223
                        href="<?= BRAND_URL; ?>locations/AR"><AREA
                        title=Louisiana shape=POLY alt=Louisiana
                        coords=264,260,265,270,270,277,271,281,270,299,312,301,308,289,304,293,302,289,303,285,302,281,290,281,290,275,291,268,291,264,288,262
                        href="<?= BRAND_URL; ?>locations/LA"><AREA
                        title=Wisconsin shape=POLY alt=Wisconsin
                        coords=268,111,266,129,280,138,282,150,308,150,303,138,308,123,307,122,300,129,299,126,302,121,302,118,282,111,282,107
                        href="<?= BRAND_URL; ?>locations/WI"><AREA
                        title=Illinois shape=POLY alt=Illinois
                        coords=282,150,308,152,312,162,312,185,313,193,312,204,303,211,302,211,299,201,291,193,293,191,290,187,286,187,282,181,287,169,290,160,282,152
                        href="<?= BRAND_URL; ?>locations/IL"><AREA
                        title=Mississippi shape=POLY alt=Mississippi
                        coords=295,237,290,251,290,259,293,263,294,271,290,279,305,279,306,285,314,283,313,234
                        href="<?= BRAND_URL; ?>locations/MS"><AREA
                        title=Michigan shape=POLY alt=Michigan
                        coords=319,154,338,154,345,136,327,112,323,103,316,101,311,105,296,98,286,105,308,117,314,107,322,107,322,113,320,120,316,120,318,152
                        href="<?= BRAND_URL; ?>locations/MI"><AREA
                        title=Indiana shape=POLY alt=Indiana
                        coords=313,160,332,156,332,175,333,185,328,191,328,195,314,197
                        href="<?= BRAND_URL; ?>locations/IN"><AREA
                        title=Tennessee shape=POLY alt=Tennessee
                        coords=302,217,299,231,342,227,350,219,363,213,362,209,314,213,312,218
                        href="<?= BRAND_URL; ?>locations/TN"><AREA
                        title=Kentucky shape=POLY alt=Kentucky
                        coords=304,214,316,201,329,199,333,190,338,184,346,186,345,186,350,190,358,198,361,202,353,207,350,209,315,211,308,217
                        href="<?= BRAND_URL; ?>locations/KY"><AREA
                        title=Ohio shape=POLY alt=Ohio
                        coords=332,156,334,179,345,185,361,174,362,163,364,159,361,146,349,158,342,158,338,155
                        href="<?= BRAND_URL; ?>locations/OH"><AREA
                        title=Alabama shape=POLY alt=Alabama
                        coords=316,233,317,283,325,282,325,278,321,272,347,272,346,265,344,261,347,257,336,231
                        href="<?= BRAND_URL; ?>locations/AL"><AREA
                        title="West Virginia" shape=POLY
                        alt="West Virginia"
                        coords=350,184,351,192,363,203,371,193,376,181,386,172,384,170,388,168,388,172,386,168,376,172,374,170,375,166,370,170,368,162
                        href="<?= BRAND_URL; ?>locations/WV"><AREA
                        title=Florida shape=POLY alt=Florida
                        coords=325,274,329,282,337,280,347,288,354,282,359,283,374,294,375,305,386,319,396,327,400,323,402,313,398,301,388,288,382,280,379,275,378,272,375,272,374,279,370,278,350,276,350,274
                        href="<?= BRAND_URL; ?>locations/FL"><AREA
                        title=Georgia shape=POLY alt=Georgia
                        coords=338,233,349,257,348,269,349,274,368,274,379,265,380,253,377,251,372,243,356,233,356,228,353,230,346,233
                        href="<?= BRAND_URL; ?>locations/GA"><AREA
                        title="South Carolina" shape=POLY
                        alt="South Carolina"
                        coords=357,228,373,241,381,253,391,241,399,228,393,228,388,223,375,223,365,222
                        href="<?= BRAND_URL; ?>locations/SC"><AREA
                        title="North Carolina" shape=POLY
                        alt="North Carolina"
                        coords=346,226,357,218,365,213,367,209,411,195,416,203,400,225,393,223,389,219,367,219,357,223,351,227
                        href="<?= BRAND_URL; ?>locations/NC"><AREA
                        title=Wyoming shape=POLY alt=Wyoming
                        coords=122,130,122,173,176,174,176,132
                        href="<?= BRAND_URL; ?>locations/WY"><AREA
                        title=Maryland shape=POLY alt=Maryland
                        coords=377,167,375,170,387,167,388,166,389,169,393,171,397,173,399,169,404,164,404,169,405,173,413,185,415,173,408,173,405,160
                        href="<?= BRAND_URL; ?>locations/MD"><AREA
                        title=Delaware shape=POLY alt=Delaware
                        coords=407,160,407,170,417,173,439,173,438,164,412,167,408,160
                        href="<?= BRAND_URL; ?>locations/DE"><AREA
                        title=Pennsylvania shape=POLY alt=Pennsylvania
                        coords=365,141,370,162,374,165,408,155,409,153,409,149,408,143,404,136,379,143,375,143,370,143,367,141
                        href="<?= BRAND_URL; ?>locations/PA"><AREA
                        title="New Jersey" shape=POLY alt="New Jersey"
                        coords=409,141,410,161,413,161,417,155,436,158,438,151,420,151,414,145,408,143
                        href="<?= BRAND_URL; ?>locations/NJ"><AREA
                        title="New York" shape=POLY alt="New York"
                        coords=369,139,375,133,373,125,381,121,393,121,397,98,408,98,413,114,416,132,417,143,428,139,432,143,420,146,408,141,404,135,371,145
                        href="<?= BRAND_URL; ?>locations/NY"><AREA
                        title=Connecticut shape=POLY alt=Connecticut
                        coords=416,130,419,138,427,134,446,155,455,155,458,148,443,146,429,132,429,128
                        href="<?= BRAND_URL; ?>locations/CT"><AREA
                        title="Rhode Island" shape=POLY
                        alt="Rhode Island"
                        coords=432,126,432,134,447,146,457,148,455,141,449,141,434,130,434,126
                        href="<?= BRAND_URL; ?>locations/RI"><AREA
                        title=Vermont shape=POLY alt=Vermont
                        coords=410,97,417,120,420,118,420,108,421,101,418,97
                        href="<?= BRAND_URL; ?>locations/VT"><AREA
                        title=Massachusetts shape=POLY
                        alt=Massachusetts
                        coords=415,120,416,128,433,126,435,128,439,134,447,124,450,120,446,114,442,118,446,126,435,118
                        href="<?= BRAND_URL; ?>locations/MA"><AREA
                        title="New Hampshire" shape=POLY
                        alt="New Hampshire"
                        coords=422,96,421,118,429,118,434,114,432,104,425,89
                        href="<?= BRAND_URL; ?>locations/NH"><AREA
                        title=Maine shape=POLY alt=Maine
                        coords=426,89,436,109,437,100,445,93,451,87,451,83,445,83,445,75,437,64,434,70,430,67,426,81
                        href="<?= BRAND_URL; ?>locations/ME"><AREA
                        title=Virginia shape=POLY alt=Virginia
                        coords=353,210,364,204,375,194,377,186,389,169,407,183,413,196,362,210
                        href="<?= BRAND_URL; ?>locations/VA"></MAP>
            </td>
        </tr>
    </table>
</form>