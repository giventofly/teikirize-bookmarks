document.addEventListener("DOMContentLoaded", function () {
  //close open modals on outside clicks
  document.querySelector("body").addEventListener("click", clickOutSideModal);
  //default buttons
  const defaultBtns = ["addbookmark", "addtag", "sortbtn", "viewbtn"];
  //add click listeners for default buttons
  defaultBtns.forEach((id) => {
    const btn = document.getElementById(id);
    btn &&
      btn.addEventListener("click", function (e) {
        e.stopPropagation();
        const modalTarget = document.getElementById(this.dataset.modal);
        //only open if it is closed
        if (!modalTarget.classList.contains("open")) {
          closeOpenModals();
          modalTarget.classList.add("open");
        } else {
          closeOpenModals();
        }
      });
  });
  document.getElementById("addbookmark").addEventListener("click", resetBookmarkModal);
  //close btns
  const closeBtns = [...document.querySelectorAll("button.close")];
  closeBtns.forEach((btn) => {
    btn.addEventListener("click", closeOpenModals);
  });
  //load options and change menus if needed
  const bootOptions = loadUserSettings();
  if (bootOptions) {
    sortMode = bootOptions.sortMode ? bootOptions.sortMode : sortMode;
    viewMode = bootOptions.viewMode ? bootOptions.viewMode : viewMode;
    orderMode = bootOptions.orderMode ? bootOptions.orderMode : orderMode;
    changeViewMode(viewMode);
  }
  //sort and view menu catch
  const sortViewMenus = [
    ...document.querySelectorAll("#sortmenu .option"),
    ...document.querySelectorAll("#viewmenu .option"),
  ];
  sortViewMenus &&
    sortViewMenus.forEach((option) => {
      //make current view mode
      if (option.dataset.mode && option.dataset.view && option.dataset.view == viewMode) {
        const menu = document.getElementById(`viewbtn`);
        removeAllChildren(menu);
        menu.appendChild(option.cloneNode(true));
      }
      if (
        option.dataset.sort &&
        option.dataset.sort == sortMode &&
        option.dataset.order == orderMode
      ) {
        const menu = document.getElementById(`sortbtn`);
        removeAllChildren(menu);
        menu.appendChild(option.cloneNode(true));
      }
      //mkae current sort mode
      option.addEventListener("click", function (e) {
        closeOpenModals();
        const mode = e.target.dataset.mode;
        const menu = document.getElementById(`${mode}btn`);
        removeAllChildren(menu);
        menu.appendChild(e.target.cloneNode(true));
        //sort mode
        if (mode === "sort") {
          let currentSort = sortMode;
          let currentOrder = orderMode;
          sortMode = e.target.dataset.sort;
          orderMode = e.target.dataset.order;
          //call for new posts in case of changes
          if (currentSort != sortMode || currentOrder != orderMode) {
            //reset page number, refetch
            currentPage = 0;
            hasMore = 1;
            getBookmarks();
          }
        }
        //view mode
        else {
          viewMode = e.target.dataset.view;
          changeViewMode(viewMode);
          if (needsToFill(getBookmarkListElem())) {
            getBookmarks();
          }
        }
        //save settings
        saveUserSettings();
        //console.log(getCurrentUserSettings());
      });
    });
  //save or update bookmark
  const insertUpdateBookmark = document.getElementById("savebookmark");
  insertUpdateBookmark && insertUpdateBookmark.addEventListener("click", saveBookmark);
  //set boot height
  minWindowHeight = windowHeight();
  //boot load bookmarks
  getBookmarkListElem && getBookmarks();
  //start infinite scroll
  infiniteScroll(getBookmarkListElem());
  //search input
  const searchInput = document.getElementById("search");
  searchInput.value = "";
  const searchbox = document.getElementById("searchbox");
  //text search
  const searchresults = document.getElementById("searchresults");
  //text to search for tags/text
  const textsearchfor = document.getElementById("textsearchfor");
  //query to search (text or tags)
  const textsearchquery = document.getElementById("textsearchquery");
  //search suggestions for input
  const searchsuggestions = document.getElementById("searchsuggestions");
  //validate search text input
  const validateTextInput = debounce((e) => {
    //const value = e.target.value.replace(",", "");
    let isTextQuery = true;
    let matches = [];
    searchTags = [];
    searchText = "";
    currentPage = 0;
    const searchQuery = searchInput.value.trim();
    const regex = /(#[\w\d]*),? ?/g;
    searchbox.classList.remove("suggesting");
    searchbox.classList.remove("clear");
    searchresults.classList.add("hide");
    if (searchQuery.length > 0) {
      // update status
      searchbox.classList.add("clear");
      //tag search
      if (searchQuery[0] === "#" && searchQuery.length > 1) {
        searchbox.classList.add("suggesting");
        textsearchfor.innerText = "Search results for tags";
        searchresults.classList.remove("hide");
        while ((match = regex.exec(searchQuery))) {
          matches.push(match[1].replace("#", ""));
        }
        //console.log(matches);
        if (matches) {
          isTextQuery = false;
          //make suggestions modal
          //console.log(searchTagSuggestions(matches[matches.length - 1]));
          removeAllChildren(searchsuggestions);
          const suggestions = searchTagSuggestions(matches[matches.length - 1]);
          if (suggestions.length == 0) {
            searchbox.classList.remove("suggesting");
          }
          suggestions.length &&
            suggestions.forEach((tag) => {
              const newtag = document.createElement("p");
              newtag.innerText = tag.tag;
              searchsuggestions.appendChild(newtag);
            });
          removeAllChildren(textsearchquery);
          const listOfTags = [];
          matches.forEach((tag) => {
            const newtag = document.createElement("span");
            newtag.innerText = "#" + tag;
            newtag.classList.add("tag");
            textsearchquery.appendChild(newtag);
            //add to tags to search
            let found = null;
            if(tagsList["list"]){
              found = tagsList["list"].find(
                (elem) => elem.tag.toLowerCase() == tag.toString().toLowerCase()
                );
              }
            if (typeof found != "undefined" && found != null) {
              listOfTags.push(found.tag);
              searchTags.push(found.tag_id);
            }
          });
          //add tags to url
          setTagsToUrl(listOfTags);

          //catch enter \\ tab to use first suggestion
          //mkae search box

          //search for tags, tag ides from search box, debounce, set count to track
        }
      }
      if (searchQuery[0] !== "#") {
        //make search box results
        textsearchfor.innerText = "Search results for text";
        textsearchquery.innerText = searchQuery;
        searchresults.classList.remove("hide");
        searchText = searchQuery;
        //set search query for url
        setSearchToUrl(searchQuery);
        //search for text, text from search box, debounce, set count to track
      }
    }
    //search only if text or tag exists
    if (searchQuery[0] !== "#" || searchTags.length > 0) {
      getBookmarks();
    }
  }, 250);
  //clear input
  searchbox &&
    searchbox.querySelector("button").addEventListener("click", function (e) {
      searchInput.value = "";
      deleteTagsAndSearch();
      searchbox.classList.remove("suggesting");
      searchbox.classList.remove("clear");
      searchresults.classList.add("hide");
      currentPage = 0;
      searchTags = "";
      searchText = "";
      getBookmarks();
    });
  //unfocus
  searchInput &&
    searchInput.addEventListener("focusout", (e) => {
      searchbox.classList.remove("suggesting");
    });
  //text
  searchInput && searchInput.addEventListener("input", validateTextInput);
  //catch keyboard
  searchInput &&
    searchInput.addEventListener("keydown", (e) => {
      //console.log(e.key);
      if (
        e.key == "Enter" ||
        (e.key == "Tab" && searchbox.classList.contains("suggesting"))
      ) {
        e.preventDefault();
        //select first entry
        const selectedTag = searchsuggestions.querySelector("p:first-child")  ? searchsuggestions.querySelector("p:first-child").innerText : "";
        const currentValue = searchInput.value;
        let tempArr = currentValue.split("#");
        tempArr.pop();
        searchInput.value = "";
        deleteTagsAndSearch();
        tempArr.forEach((item) => {
          searchInput.value += item ? `#${item.trim()} ` : "";
        });
        searchInput.value += `#${selectedTag}`;
        validateTextInput();
        searchbox.classList.remove("suggesting");
      }
      if (e.key == "Escape") {
        searchbox.classList.remove("suggesting");
      }
    });

  //BOOKMARK INSERTION, make this programatically with the searchbox too?
  const validateTextInputBookmark = (e) => {
    if (!e) {
      return false;
    }
    //const value = e.target.value.replace(",", "");
    let matches = [];
    const searchQuery = tagsBookmark.value
      .replace(/, /g, ",")
      .replace(/ /g, "-")
      .replace(/#/g, "");
    e.target.value = searchQuery;
    const regex = /([\w\d]+),? ?/g;
    searchsuggestionsbookmark.classList.remove("active");
    if (searchQuery.length > 2) {
      while ((match = regex.exec(searchQuery))) {
        matches.push(match[1].replace(",", ""));
      }
      if (matches && matches[matches.length - 1].length > 2) {
        searchsuggestionsbookmark.classList.add("active");
        removeAllChildren(searchsuggestionsbookmark);
        const suggestions = searchTagSuggestions(matches[matches.length - 1], 3);
        //console.log(suggestions.length);
        if (suggestions.length == 0) {
          searchsuggestionsbookmark.classList.remove("active");
        }
        suggestions.length &&
          suggestions.forEach((tag) => {
            const newtag = document.createElement("p");
            newtag.innerText = tag.tag;
            searchsuggestionsbookmark.appendChild(newtag);
          });
      }
    }
  };
  const tagsBookmark = document.getElementById("tags");
  const searchsuggestionsbookmark = document.getElementById("searchsuggestionsbookmark");
  tagsBookmark && tagsBookmark.addEventListener("input", validateTextInputBookmark);
  tagsBookmark &&
    tagsBookmark.addEventListener("keydown", (e) => {
      //console.log(e.key);
      if (
        e.key == "Enter" ||
        (e.key == "Tab" && searchsuggestionsbookmark.classList.contains("active"))
      ) {
        e.preventDefault();
        //select first entry
        const selectedTag =
          searchsuggestionsbookmark.querySelector("p:first-child").innerText;
        const currentValue = tagsBookmark.value;
        let tempArr = currentValue.split(",");
        tempArr.pop();
        tagsBookmark.value = "";
        tempArr.forEach((item) => {
          tagsBookmark.value += item ? `${item.trim()},` : "";
        });
        tagsBookmark.value += `${selectedTag}`;
        validateTextInputBookmark();
      }
      searchsuggestionsbookmark.classList.remove("active");
      if (e.key == "Escape") {
        searchsuggestionsbookmark.classList.remove("active");
      }
    });
  tagsBookmark &&
    tagsBookmark.addEventListener("focusout", (e) => {
      searchsuggestionsbookmark.classList.remove("active");
    });
  //required fields
  const url = document.getElementById("url");
  url && url.addEventListener("input", getMediaInfo);
  //load tags or search on page load from page url
  const urlParams = new URLSearchParams(window.location.search);
  const tags = urlParams.get("tags");
  const search = urlParams.get("search");
  if (tags) {
    tags.split(",").forEach((tag) => {
      searchInput.value += `#${tag} `;
    });
    validateTextInput();
  }
  if (search) {
    searchInput.value = search;
    validateTextInput();
  }

  //tags merge
  const mergeTagBtn = document.getElementById("mergetag");
  mergeTagBtn && mergeTagBtn.addEventListener("click", function(e){
    mergeTag(e.target.dataset.tagid,document.getElementById('tagsmergelist').value);
    //console.log("merge",e.target.dataset.tagid,document.getElementById('tagsmergelist').value);
  });

});
