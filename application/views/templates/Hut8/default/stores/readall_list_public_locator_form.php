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
                <img src="/css/<?= $_SESSION['brand']['brandLabel']; ?>/images/items/store_locator_map.png" alt="" width="632" height="424" border="0" usemap="#MapStore" class="no-border" />

                <!-- HUT STORE LOCATOR MAP -->
                <map name="MapStore" id="MapStore">
                    <area shape="poly" coords="403,0,440,1,443,0,403,0" href="<?= BRAND_URL; ?>locations/VT" target="_parent" alt="Vermont <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="414,256,418,260,419,264,424,263,428,263,430,262,431,259,433,259,436,261,437,262,439,261,440,261,442,261,445,262,448,259,449,262,452,265,454,268,455,269,459,269,462,266,465,265,466,262,468,260,469,258,473,258,478,259,484,259,488,261,494,265,498,268,501,269,506,269,509,275,510,280,511,286,512,289,514,289,515,289,516,287,517,285,519,284,521,285,522,286,520,290,522,293,523,295,526,298,528,301,532,302,534,301,537,303,539,306,540,308,541,309,544,308,545,309,546,313,547,314,550,315,553,316,555,316,558,316,558,317,561,316,563,316,565,319,568,322,572,324,577,327,578,328,580,327,582,324,584,324,586,325,587,325,589,322,588,319,588,318,586,318,584,318,582,317,581,313,581,309,580,305,579,300,577,296,574,293,570,290,568,287,567,285,564,284,558,279,554,275,549,272,546,270,545,270,546,268,545,266,542,265,536,262,532,258,527,255,524,252,518,247,515,244,513,242,511,240,508,241,507,242,505,245,505,245,504,247,502,245,499,245,463,250,460,252,456,251,454,250,452,249,417,255" href="<?= BRAND_URL; ?>locations/FL" target="_parent" alt="Florida <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="391,202,402,201,421,196,429,206,432,211,437,219,442,225,445,231,447,241,450,246,412,253,414,259,418,262,414,264,410,264,406,256,409,268,405,270,400,269,400,264,403,254,399,236,393,207" href="<?= BRAND_URL; ?>locations/AL" target="_parent" alt="Alabama <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="507,235,505,233,506,231,510,231,512,232,514,231,514,228,512,225,511,222,512,220,511,217,510,216,512,213,511,212,508,210,506,208,505,206,499,203,491,199,487,197,481,195,475,192,470,190,467,190,465,189,463,180,457,182,447,185,437,188,430,188,422,189,425,194,435,209,441,216,446,223,447,231,450,236,453,241,456,244,462,245" href="<?= BRAND_URL; ?>locations/GA" target="_parent" alt="Georgia <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="465,182,483,177,491,181,506,177,530,183,527,191,522,201,510,211,506,207,499,202,480,194,466,190,465,185" href="<?= BRAND_URL; ?>locations/SC" target="_parent" alt="South Carolina <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="439,187,440,182,443,179,447,175,453,174,456,169,458,167,463,165,466,162,467,159,468,156,489,152,507,148,523,142,535,139,542,137,545,138,545,141,540,144,539,145,543,145,548,142,552,141,552,145,551,149,548,151,546,152,547,154,551,153,553,154,552,156,549,158,545,162,543,164,541,167,540,171,540,173,535,176,530,178,519,174,503,171,491,175,480,171,444,183" href="<?= BRAND_URL; ?>locations/NC" target="_parent" alt="North Carolina <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="400,245,388,192,387,181,356,185,353,190,351,195,349,201,347,203,345,205,349,223,351,223,350,226,349,228,348,232,345,236,346,239,346,242,345,243,366,241,377,238,375,245,380,251,387,250,393,244" href="<?= BRAND_URL; ?>locations/MS" target="_parent" alt="Mississippi <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="302,239,303,242,308,248,312,255,310,257,313,263,318,266,318,269,315,275,315,284,311,291,324,286,333,288,341,289,344,285,346,283,353,285,361,290,360,293,364,290,366,294,372,288,377,292,379,291,378,287,376,283,380,283,384,293,394,290,392,292,395,293,396,289,395,286,393,285,386,288,387,285,386,281,388,276,386,274,382,279,380,277,382,274,380,272,374,269,376,265,376,263,374,262,349,266,344,245,344,240,315,242" href="<?= BRAND_URL; ?>locations/LA" target="_parent" alt="Louisiana <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="353,189,298,197,302,240,346,237,350,227,343,210,345,200,349,198,350,194" href="<?= BRAND_URL; ?>locations/AR" target="_parent" alt="Arkansas <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="422,197,419,188,438,188,439,183,440,179,445,176,451,174,454,171,455,169,429,176,389,183,390,201,406,199" href="<?= BRAND_URL; ?>locations/TN" target="_parent" alt="Tennessee <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="300,234,292,233,288,235,280,234,280,237,274,235,266,236,257,235,254,235,242,233,234,231,228,226,225,219,226,206,198,207,185,262,137,263,141,269,144,275,148,279,151,281,156,284,154,288,155,293,155,296,158,300,163,302,171,306,174,306,176,298,180,296,185,294,194,294,199,296,206,300,210,303,213,310,211,315,216,320,220,325,222,327,223,331,223,336,229,340,227,348,228,350,233,351,239,350,244,353,251,355,257,357,258,355,256,346,256,340,255,334,256,327,258,323,259,320,263,317,267,315,270,313,275,311,278,309,284,306,290,302,293,300,295,297,296,295,295,292,299,291,303,294,308,292,312,290,311,287,311,283,313,279,315,273,316,270,314,267,309,264,308,259,309,255,309,254,307,249,303,244,303,240,302,238" href="<?= BRAND_URL; ?>locations/TX" target="_parent" alt="Texas <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="195,192,195,200,228,197,225,218,228,220,230,222,233,222,235,222,236,224,242,223,249,224,252,224,251,226,257,226,258,227,263,224,270,225,273,227,276,227,277,224,281,224,287,224,292,224,295,224,296,225,300,224,295,184" href="<?= BRAND_URL; ?>locations/OK" target="_parent" alt="Oklahoma <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="98,262,108,263,113,257,131,257,132,254,180,255,194,192,128,194" href="<?= BRAND_URL; ?>locations/NM" target="_parent" alt="New Mexico <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="85,199,81,209,76,207,71,212,69,216,68,220,68,225,64,229,62,232,61,235,57,239,55,240,57,244,53,247,51,248,53,251,61,254,67,259,78,265,83,268,93,269,103,269,96,263,124,202,96,200" href="<?= BRAND_URL; ?>locations/AZ" target="_parent" alt="Arizona <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="66,218,67,225,64,228,61,231,60,234,59,236,56,236,53,237,53,240,54,243,51,245,27,244,25,242,27,238,28,236,26,235,25,234,25,232,24,231,20,231,21,227,22,224,18,224,16,223,14,220,11,219,8,218,9,215,11,212,12,210,13,208,10,207,9,205,10,203,9,199,11,196,13,194,16,193,13,190,16,186,16,167,18,165,21,163,22,159,22,157,29,148,35,141,50,143,35,161,54,205" href="<?= BRAND_URL; ?>locations/CA" target="_parent" alt="California <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="76,187,69,197,65,195,56,206,37,161,57,133,96,136,71,177" href="<?= BRAND_URL; ?>locations/NV" target="_parent" alt="Nevada <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="140,139,121,141,115,132,97,132,73,178,120,181" href="<?= BRAND_URL; ?>locations/UT" target="_parent" alt="Utah <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="200,178,194,165,201,135,141,139,120,182,128,181,193,178" href="<?= BRAND_URL; ?>locations/CO" target="_parent" alt="Colorado <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="289,132,283,129,283,126,279,124,209,131,204,133,202,136,196,162,195,166,200,166,284,156,288,156,290,155" href="<?= BRAND_URL; ?>locations/KS" target="_parent" alt="Kansas <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="291,131,294,176,350,168,355,167,356,170,354,172,358,173,362,172,363,169,363,166,366,165,367,164,366,159,362,158,358,150,348,150,346,148,347,144,349,143,345,141,341,141,339,141,339,138,334,135,330,133,329,133,328,128,330,127,326,124,323,127" href="<?= BRAND_URL; ?>locations/MO" target="_parent" alt="Missouri <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="71,54,67,57,66,60,64,64,62,63,59,62,56,60,54,58,51,63,51,67,50,69,47,72,47,75,51,76,56,75,60,75,63,78,69,78,74,77,81,75,87,75,90,80,95,79,98,75,98,71,100,67,104,65,105,62,102,59,101,56" href="<?= BRAND_URL; ?>locations/WA" target="_parent" alt="Washington <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="79,135,75,129,78,124,82,119,85,113,88,110,88,109,88,106,99,99,99,96,98,94,100,92,101,89,96,88,93,86,88,85,84,85,80,86,74,87,71,87,70,87,65,86,61,85,56,84,53,83,53,89,52,93,51,98,49,103,47,107,46,111,44,116,42,120,41,122,38,126,38,130,39,134,37,138,35,142,45,143,48,143,50,139,52,136,54,134,56,133,59,133,63,134,67,134,71,134,74,134" href="<?= BRAND_URL; ?>locations/OR" target="_parent" alt="Oregon <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="122,108,120,104,115,98,113,97,111,93,110,89,107,87,104,85,102,88,100,91,100,94,99,97,100,99,99,100,94,103,92,104,89,108,89,109,87,112,85,115,82,118,80,122,79,124,77,126,75,128,113,131,120,112" href="<?= BRAND_URL; ?>locations/ID" target="_parent" alt="Idaho <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="180,123,173,110,182,91,181,87,135,89,132,90,126,102,118,121,116,127" href="<?= BRAND_URL; ?>locations/WY" target="_parent" alt="Wyoming <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="126,75,125,79,122,79,117,80,115,79,109,81,107,78,106,75,101,75,100,72,100,68,105,66,106,63,104,60,102,54,101,50,106,43,111,42,199,38,198,46,186,46,177,73" href="<?= BRAND_URL; ?>locations/MT" target="_parent" alt="Montana <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="182,90,174,109,200,108,204,86" href="<?= BRAND_URL; ?>locations/SD" target="_parent" alt="South Dakota <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="177,75,256,71,259,60,259,40,257,37,186,46" href="<?= BRAND_URL; ?>locations/ND" target="_parent" alt="North Dakota <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="271,110,270,106,269,104,268,99,266,95,262,89,261,86,258,85,255,84,250,84,246,85,241,83,238,83,229,85,222,85,216,85,204,87,199,118,265,109" href="<?= BRAND_URL; ?>locations/NE" target="_parent" alt="Nebraska <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="270,96,270,80,266,54,269,50,312,64,312,78,315,87" href="<?= BRAND_URL; ?>locations/MN" target="_parent" alt="Minnesota <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="269,97,270,107,276,110,279,123,328,117,339,104,341,97,329,96,326,91,323,86" href="<?= BRAND_URL; ?>locations/IA" target="_parent" alt="Iowa <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="361,183,391,176,394,180,440,169,443,152,425,149,417,146,413,156,406,155,398,160,392,162,385,164,374,171,368,175,366,174,360,179" href="<?= BRAND_URL; ?>locations/KY" target="_parent" alt="Kentucky <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="333,124,332,130,330,132,334,134,338,136,339,138,340,140,345,140,349,141,350,144,348,146,350,147,353,148,359,149,361,153,363,156,366,158,367,159,367,164,367,166,373,166,374,165,373,162,375,161,377,160,377,157,377,154,378,153,379,151,378,149,379,147,380,145,379,141,376,138,376,135,375,129,374,126,373,122,372,118,372,115,370,111,366,108,356,110,346,111,344,111,343,116,343,118,340,120,338,121,336,123" href="<?= BRAND_URL; ?>locations/IL" target="_parent" alt="Illinois <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="373,115,374,123,376,132,376,138,381,143,381,148,379,153,386,154,395,152,404,147,409,140,416,138,400,106,381,110,376,113" href="<?= BRAND_URL; ?>locations/IN" target="_parent" alt="indiana <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="419,129,418,139,429,142,443,143,445,144,452,140,455,135,459,131,464,132,473,131,478,128,481,125,481,119,472,121,472,116,466,117,461,114,456,118,453,121,446,123,438,124,432,126,429,128,420,128" href="<?= BRAND_URL; ?>locations/WV" target="_parent" alt="West Virginia <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="482,119,482,126,479,129,474,132,468,132,462,132,459,133,457,135,455,138,454,139,451,141,448,144,456,144,465,142,478,139,493,134,498,133,508,132,521,127,531,124,542,120,548,119,546,116,540,116,535,111,531,108,525,104,519,104,515,105,516,100,511,98,504,98,496,97,491,104,487,111,483,115" href="<?= BRAND_URL; ?>locations/VA" target="_parent" alt="Virginia <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="324,87,330,97,341,97,340,103,336,111,337,119,338,120,341,119,343,116,343,111,347,110,355,109,361,109,363,106,358,103,357,94,356,91,361,41,373,42,374,33,334,22,333,32,357,40,349,89,329,89" href="<?= BRAND_URL; ?>locations/WI" target="_parent" alt="Wisconsin <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="394,77,389,75,386,80,383,84,378,86,384,90,384,100,383,107,399,106,403,108,403,97,405,91,405,84,403,78,398,77,403,52,415,51,421,48,417,40,388,32,381,32,377,41,401,48,396,74" href="<?= BRAND_URL; ?>locations/MI" target="_parent" alt="Michigan <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="451,75,446,79,441,83,435,85,428,87,420,87,414,86,409,88,406,92,406,104,411,116,417,117,423,118,430,119,437,116,444,117,447,119,448,115,454,115,454,110,461,102,461,94,460,90,455,85,452,80" href="<?= BRAND_URL; ?>locations/OH" target="_parent" alt="Ohio <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="554,111,548,121,588,132,594,120" href="<?= BRAND_URL; ?>locations/MD" target="_parent" alt="Maryland <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="553,97,549,110,591,119,595,108" href="<?= BRAND_URL; ?>locations/DE" target="_parent" alt="Delaware <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="455,82,462,90,461,101,464,106,523,89,527,81,518,76,518,73,514,72,508,68" href="<?= BRAND_URL; ?>locations/PA" target="_parent" alt="Pennsylvania <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="505,20,495,25,488,30,490,39,488,44,472,50,466,54,464,59,458,64,458,67,473,63,505,51,511,52,524,54,537,54,536,51,528,39,524,33,517,30,510,28,508,25" href="<?= BRAND_URL; ?>locations/NY" target="_parent" alt="New York <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="555,86,551,96,598,109,605,100" href="<?= BRAND_URL; ?>locations/NJ" target="_parent" alt="New Jersy <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="561,75,557,87,609,101,617,91" href="<?= BRAND_URL; ?>locations/CT" target="_parent" alt="Connecticut <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="563,65,561,75,618,91,622,79" href="<?= BRAND_URL; ?>locations/RI" target="_parent" alt="Rhode Island <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="561,52,556,64,621,79,624,69" href="<?= BRAND_URL; ?>locations/MA" target="_parent" alt="Massachusetts <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="561,36,557,50,625,67,628,54" href="<?= BRAND_URL; ?>locations/NH" target="_parent" alt="New Hampshire <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="526,24,545,43,544,51,562,37,570,16,534,10" href="<?= BRAND_URL; ?>locations/ME" target="_parent" alt="Maine <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                    <area shape="poly" coords="509,22,515,30,523,32,535,50,540,47,525,25,528,11,504,4,487,5,491,10,510,16" href="<?= BRAND_URL; ?>locations/VT" target="_parent" alt="Vermont <?= $_SESSION['brand']['brandName']; ?> Store Locations" />
                </map>
            </td>
        </tr>
    </table>
</form>