<div class="withmargins">
  <!-- filters -->
  <div class="filters" id="filters">
    <!-- search text/tags -->
    <div class="searchbox" id="searchbox">
      <img src="<?php echo get_base_url(); ?>/assets/icons/search.svg" alt="">
      <input type="text" placeholder="Search with text or #tags #tag2 #tag3 ..." id="search">
      <button class="clear"><img src="<?php echo get_base_url(); ?>/assets/icons/close.svg" alt=""></button>
      <div id="searchsuggestions" class="f-light"></div>
    </div>
    <!-- sort and view -->
    <div class="sortandview" id="sortandview">
      <!-- sort block -->
      <div class="block">
        <div name="" data-modal="sortmenu" id="sortbtn" class="selector"></div>      
        <!-- sort menu modal -->
        <div class="menu-options f-light modal" id="sortmenu">
          <div class="text">Sort by</div>
          <div class="option" data-sort="name" data-order="asc" data-mode="sort">
            <img src="<?php echo get_base_url(); ?>/assets/icons/alpha_asc.svg" alt="">
              Name (A-Z)
          </div>
          <div class="option" data-sort="name" data-order="desc" data-mode="sort">
            <img src="<?php echo get_base_url(); ?>/assets/icons/alpha_desc.svg" alt="">
              Name (Z-A)
          </div>
          <div class="option" data-sort="date" data-order="asc" data-mode="sort">
            <img src="<?php echo get_base_url(); ?>/assets/icons/num_asc.svg" alt="">
              Added (0-9)
          </div>
          <div class="option" data-sort="date" data-order="desc" data-mode="sort">
            <img src="<?php echo get_base_url(); ?>/assets/icons/num_desc.svg" alt="">
              Added (9-0)
          </div>
        </div>
      </div>
      <!-- view block -->
      <div class="block">
        <div name="" data-modal="viewmenu" id="viewbtn" class="selector"></div>
        <!-- view menu modal -->
        <div class="menu-options f-light modal" id="viewmenu" >
          <div class="text">View as</div>
          <div class="option" data-view="grid" data-mode="view">
            <img src="<?php echo get_base_url(); ?>/assets/icons/grid.svg" alt="">
              Grid
          </div>
          <div class="option" data-view="list" data-mode="view">
            <img src="<?php echo get_base_url(); ?>/assets/icons/list.svg" alt="">
              List
          </div>
        </div>
      </div>

    </div>
  </div>
  <!-- buttons -->
  <div id="buttons-wrapper">
    <div class="buttons withmargins">
      <a id="gototop" class="btn" href="#header">
        <img src="<?php echo get_base_url(); ?>/assets/icons/top.svg" alt="">
      </a>
      <button data-modal="tagsmodal" id="addtag" class="btn "><img src="<?php echo get_base_url(); ?>/assets/icons/tags.svg" alt=""></button>
      <button data-modal="bookmarkmodal" id="addbookmark" class="btn "><img src="<?php echo get_base_url(); ?>/assets/icons/add.svg" alt=""></button>
    </div>
  </div>
</div>
<div class="withmargins hide" id="searchresults">
  <div class="results" >
    <div class="search f-light" id="textsearchfor">Search results for</div>
    <div class="query" id="textsearchquery"></div>
     </div>
</div>
<div class="withmargins biggermargins">
  <!-- warning private mode is activated -->
  <?php if(PRIVATE_MODE && !is_logged_in()){ ?> 
    <div class="private-mode f-black">Private mode is active</div>
  <?php } ?>
  <!-- bookmarks zone -->
  <div class="bookmarks-list grid" id="bookmarks">
    
  </div>
</div>
<!-- modals -->
<!-- add/edit bookmark -->
<div id="bookmarkmodal" class="modal">
  <button class="close btn">
    <img src="<?php echo get_base_url(); ?>/assets/icons/close.svg" alt="">
  </button>
  <div class="text">Insert bookmark</div>
  <label for="url">
    <span class="">Link *</span>
    <input type="text" name="url" id="url" placeholder="" required>
    <img src="<?php echo get_base_url(); ?>/assets/icons/loading.svg" alt="" class="load">
  </label>
  <label for="title">
    <span class="">Title *</span>
    <input type="text" name="title" id="title" placeholder="" required>
  </label>
  <label for="description">
    <span class="">description</span>
  <input type="text" name="description" id="description" placeholder="">
  </label>

  <label for="tags">
    <span class="">tags</span>
    <input type="text" name="tags" id="tags" placeholder="e.g. work, javascript, interview" autocomplete="off">
    <div id="searchsuggestionsbookmark" class="f-light"></div>
  </label>

  <label for="image">
    <span class="">image link</span>
    <input type="text" name="image" id="image" placeholder="">
  </label>

  <label for="favicon">
    <span class="">favicon link</span>
  <input type="text" name="favicon" id="favicon" placeholder="">
  </label>

  <label for="notes">
    <span class="">Notes</span>
    <input name="notes" id="notes"  placeholder=""></input>
  </label>
  <button class="btn save f-bold" id="savebookmark">insert</button>
</div>
<!-- add/edit tags-->
<div id="tagsmodal" class="modal">
  <button class="close btn">
    <img src="<?php echo get_base_url(); ?>/assets/icons/close.svg" alt="">
  </button>
  <div class="text">Edit tags</div>
  <div class="list" id="tagslist">
    
    <!-- list end -->
  </div>
</div>
<!-- toast message -->
<div id="toastmessage"></div>


