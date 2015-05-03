#v0.0.1
 - [x] humans.txt, robots.txt
 - [x] 403,404,500.html
 - [x] MVC API Framework accessible via PHP or AJAX for dynamic content
 - [x] Brands, Domains, Usergroups, Users, Pages, Articles, Comments
 - [x] Custom Sessions stored encrypted in database
 - [x] Detect URL -> DomainName HTTP_HOST -> DomainID -> Brand ID
 - [x] ACL
 - [x] Optional $template variable added to Controller class to allow specifying whether to load header/footer, useful for AJAX requests returning JSON datatype
 - [x] WYSIWYG (TinyMCE + Responsive File Manager)
 - [x] Public and Private folders per Brand and Store
 - [x] Restrict content (Pages, Articles) to that brand ID
 - [x] URL Rewrites => /about <==> /pages/view/2/2014/05/23/about-page
 - [x] Brand Theme support
 - [x] Forgot password
 - [x] Store Locator
 - [x] favicon per brand
  
#@TODO
 - [ ] Stores Territory Maps
 - [ ] Cart integration
 - [ ] Admin: Dynamic Brand selection list (domain etc., ACL to view/change)
 - [/] Sanitization (audit all input sanitization and usage)
 - [ ] CSS editor (move favicon to templates table too)
 - [/] Model - aclWhere - Tables that do NOT have brandID column but still need to be restricted by the associated brand
 - [ ] Filemanager/dialog -- tie into database
 - [ ] Check for updates
 - [ ] v1.0.0