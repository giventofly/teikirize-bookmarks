//global variables
let sortMode = "name";
let orderMode = "asc";
let viewMode = "grid";
let currentPage = 0;
let searchTags = [];
let searchText = "";
let tagsList = null;
let firstFetch = true;
let hasMore = 1;
let isFetching = false;
let minWindowHeight = 0;
let threshold = 200;

//get current user settings
const getCurrentUserSettings = () => {
  return { sortMode, orderMode, viewMode };
};
//save in localstorage user settings
const saveUserSettings = () => {
  localStorage.setItem("teikirizeBookmarks", JSON.stringify(getCurrentUserSettings()));
};
//get from localstorage user settings
const loadUserSettings = () => {
  return JSON.parse(localStorage.getItem("teikirizeBookmarks"));
};
//remove http(s) and www. from url
const stripURL = (url) => {
  return url
    .replace(/^(https?:\/\/)?(wwww\.)?/, "")
    .replace("www.", "")
    .replace(/\/$/, "");
};
//get hostname and procotol from url
const getHostnameProtocol = (url) => {
  return url.split("/").slice(0, 3).join("/");
};
//make bookmark element
const makeBookmark = (bookmark) => {
  let element = document.createElement("div");
  element.classList.add("bookmark");
  //list of tags
  let tags_html = "";
  let tags_text = "";
  bookmark.tags.forEach((tag) => {
    if (tagsList.list.length && tagsList.list[tagsList.index[tag]]) {
      tags_html += `<span class="tag ">
                      #${tagsList.list[tagsList.index[tag]].tag}
                    </span>`;
      tags_text += `${tagsList.list[tagsList.index[tag]].tag},`;
    }
  });
  element.dataset.uuid = bookmark.uuid;
  element.innerHTML = `
      <a href="${
        bookmark.url
      }" class="bookmark-content" target="_blank" rel="nofollow noopener">
        <!-- featured image and favicon -->
        <div class="images">
          <img 
            class="featured yall_lazy ${bookmark.image ? "" : "fixed"}" 
            src="${
              bookmark.image ? bookmark.image : base_url + "/assets/icons/image.png"
            }" 
            alt=""
          >
        </div>
        <div class="info">
          <div class="title f-bold">${bookmark.title}</div>
          <div class="description f-light">${bookmark.description}</div>
          ${bookmark.notes ? '<div class="notes">' + bookmark.notes + "</div>" : ""}
          <div class="tags">${tags_html}</div>
          <div class="meta-info">
            <div class="favicon">
              <img 
                  class=" ${bookmark.favicon ? "" : "fixed"}" 
                  src="${
                    bookmark.favicon
                      ? bookmark.favicon
                      : base_url + "/assets/icons/favicon.svg"
                  }" 
                  alt=""
              >
            </div>
            <div class="url " title="${stripURL(bookmark.url)}">
              ${stripURL(bookmark.url)}
            </div>
            <div class="date ">${timeAgo(bookmark.date)}</div>
          </div>
        </div>
      </a>
      <div class="options btn" >
        <img class="option-menu-image" src="${base_url}/assets/icons/more.svg" alt="">
        <div class="menu-options f-light modal">
          <div class="option edit"
            data-url="${bookmark.url}"
            data-uuid="${bookmark.uuid}"
            data-tags="${tags_text}"
            data-favicon="${bookmark.favicon}"
            data-title="${bookmark.title}"
            data-notes="${bookmark.notes.replace(/<!--[\s\S]*?-->/g, "")}"
            data-description="${bookmark.description.replace(/<!--[\s\S]*?-->/g, "")}"
            data-image="${bookmark.image}"
            >
            <img src="${base_url}/assets/icons/edit.svg" alt="">
             edit
          </div>
          <div class="option copy" data-url="${bookmark.url}">
            <img src="${base_url}/assets/icons/share.svg" alt="">
             copy link
          </div>
          <span></span>
          <div class="option warning" data-uuid="${bookmark.uuid}">
            <img src="${base_url}/assets/icons/trash.svg" alt="">
             delete
          </div>
        </div>
      </div>
    `;
  //add listeners
  element.querySelector(".options.btn").addEventListener("click", function (e) {
    e.stopPropagation();
    closeMenuModals();
    element.querySelector(".menu-options").classList.add("open");
  });
  //copy url
  element.querySelector(".option.copy").addEventListener("click", copyLinkToshare);
  //delete URL
  element.querySelector(".option.warning").addEventListener("click", deleteBookmark);
  //edit bookmark
  element.querySelector(".option.edit").addEventListener("click", (e) => {
    e.stopPropagation();
    closeMenuModals();
    document.getElementById("url").disabled = true;
    document.getElementById("url").value = e.target.dataset.url;
    document.getElementById("image").value = e.target.dataset.image;
    document.getElementById("savebookmark").dataset.uuid = e.target.dataset.uuid;
    document.getElementById("savebookmark").innerText = "Update";
    document.querySelector("#bookmarkmodal .text").innerText = "Update bookmark";
    document.getElementById("tags").value = e.target.dataset.tags;
    document.getElementById("favicon").value = e.target.dataset.favicon;
    document.getElementById("title").value = e.target.dataset.title;
    document.getElementById("notes").value = e.target.dataset.notes;
    document.getElementById("description").value = e.target.dataset.description;
    document.getElementById("bookmarkmodal").classList.add("open");
  });

  //return element
  return element;
};
//copy bookmark link
const copyLinkToshare = (e) => {
  e.stopPropagation();
  closeMenuModals();
  toastMessage("URL copied");
  copyToClipBoard(e.target.dataset.url);
};
//delete bookmark
const deleteBookmark = (e) => {
  e.stopPropagation();
  //remove
  const params = {
    uuid: e.target.dataset.uuid,
    delete_bookmark: true,
  };
  //console.log(params);
  const formData = new FormData();
  Object.keys(params).forEach((key) => {
    formData.append(key, params[key]);
  });
  axios
    .post(`${base_url}/api/manage`, formData, {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
    })
    .then(function (response) {
      if (response.data) {
        e.target.parentNode.parentNode.parentNode.remove();
        toastMessage("Deleted successfully");
      } else {
        toastMessage("Error, something went wrong:" + response.data);
      }
    })
    .catch(function (error) {
      console.log(error);
    });
};

//close open modal on outside click
const clickOutSideModal = (e = null) => {
  let current = e.target;
  let insideModal = false;
  if (current.classList.contains("modal")) {
    return true;
  }
  while (
    current &&
    current.tagName != "BODY" &&
    current.parentNode &&
    !insideModal &&
    current.parentNode.tagName != "BODY"
  ) {
    if (current.parentNode.classList.contains("modal")) {
      insideModal = true;
    }
    current = current.parentNode;
  }
  if (!insideModal) {
    closeOpenModals();
  }
};
//close modal
const closeOpenModals = () => {
  [...document.querySelectorAll(".modal.open")].forEach((modal) => {
    modal.classList.remove("open");
  });
};
//close menu options
const closeMenuModals = () => {
  [...document.querySelectorAll(".menu-options.open")].forEach((modal) => {
    modal.classList.remove("open");
  });
};
//copy text to clipboard
const copyToClipBoard = (text2copy = "") => {
  var textarea = document.createElement("textarea");
  textarea.textContent = text2copy;
  textarea.style.position = "fixed"; // Prevent scrolling to bottom of page in MS Edge.
  document.body.appendChild(textarea);
  textarea.select();
  try {
    return document.execCommand("copy"); // Security exception may be thrown by some browsers.
  } catch (ex) {
    console.warn("Copy to clipboard failed.", ex);
    return false;
  } finally {
    document.body.removeChild(textarea);
    //toastMessage("Copied to clipboard");
  }
};
//remove all elements from element
const removeAllChildren = (elem) => {
  if (elem) {
    while (elem.hasChildNodes()) {
      elem.removeChild(elem.firstChild);
    }
  }
};
//calc time ago from unix time
const timeAgo = (unixtime) => {
  //no weeks
  const periods = ["second", "minute", "hour", "day", "month", "year", "decade"];
  const lengths = ["60", "60", "24", "31", "12", "10"];
  //js returns in ms instead of s
  const now = Math.round(Date.now() / 1000);
  let difference = now - unixtime;
  let j = 0;
  for (j = 0; difference >= lengths[j] && j < lengths.length - 1; j++) {
    difference = difference / lengths[j];
    //console.log(j, difference, lengths[j], lengths.length, difference >= lengths[j]);
  }
  difference = Math.round(difference);
  if (difference != 1) {
    periods[j] += "s";
  }
  return difference + " " + periods[j] + " ago";
};
//set view mode
const changeViewMode = (view = "grid") => {
  const bookmarks = getBookmarkListElem();
  view == "grid" ? bookmarks.classList.remove("list") : bookmarks.classList.add("list");
};
//get bookmarks
const getBookmarks = async () => {
  //make url
  const params = {
    sort: sortMode,
    order: orderMode,
    page: currentPage,
    tags: searchTags ? searchTags.join(",") : searchTags,
  };
  //search text
  searchText ? (params.query = searchText) : null;
  if (currentPage == 0 && firstFetch) {
    params.withtags = true;
  }
  try {
    isFetching = true;
    const response = await axios.get(
      `${base_url}/api/bookmarks${encodeURLparams(params)}`
    );
    //error or private mode
    if (typeof response.data.error == "undefined") {
      //if page == 0, remove all
      const bookmarkElem = getBookmarkListElem();
      if (currentPage == 0) {
        removeAllChildren(bookmarkElem);
        if (firstFetch) {
          firstFetch = false;
          tagsList = response.data.tags;
          populateTags();
        }
      }
      //insert new results
      response.data.bookmarks.forEach((bookmark, i) => {
        const newBookmark = makeBookmark(bookmark);
        bookmarkElem.appendChild(newBookmark);
        requestAnimationFrame(() => {
          setTimeout(() => {
            newBookmark.classList.add("loaded");
          }, 50 * i);
        });
      });
      //update page
      currentPage++;
      //has more to load?
      hasMore = response.data.has_more;
      isFetching = false;
      //console.log("page",currentPage,response.data.has_more);
      //fill screen on first load

      if (
        hasMore &&
        minWindowHeight == windowHeight() &&
        needsToFill(getBookmarkListElem())
      ) {
        getBookmarks();
      }
    }
  } catch (error) {
    console.error(error);
  }
};
//make url params
const encodeURLparams = (data) => {
  return (
    "?" +
    Object.keys(data)
      .map(function (key) {
        return [key, data[key]].map(encodeURIComponent).join("=");
      })
      .join("&")
  );
};
//get bookmark list element
const getBookmarkListElem = () => document.getElementById("bookmarks");
//reset bookmark modal
const resetBookmarkModal = () => {
  document.getElementById("url").value = "";
  document.getElementById("url").disabled = false;
  document.getElementById("title").value = "";
  document.getElementById("description").value = "";
  document.getElementById("image").value = "";
  document.getElementById("favicon").value = "";
  document.getElementById("notes").value = "";
  document.getElementById("tags").value = "";
  document.getElementById("savebookmark").dataset.uuid = "";
  document.getElementById("savebookmark").innerText = "insert";
  document.querySelector("#bookmarkmodal .text").innerText = "Insert bookmark";
};
//save or update bookmark
const saveBookmark = (e) => {
  if (!document.getElementById("url").value || !document.getElementById("title").value) {
    document.getElementById("url").placeholder = "THIS FIELD IS REQUIRED";
    document.getElementById("title").placeholder = "THIS FIELD IS REQUIRED";
    toastMessage("URL and title are mandatory");
    return false;
  }
  e.target.classList.add("loading");
  const bookmarkUUID = e.target.dataset.uuid ? e.target.dataset.uuid : null;
  const updateOrInsert = bookmarkUUID ? "update_bookmark" : "insert_bookmark";
  const url = document.getElementById("url").value;
  const title = document.getElementById("title").value;
  const description = document.getElementById("description").value;
  const feat_image = document.getElementById("image").value;
  const favicon = document.getElementById("favicon").value;
  const notes = document.getElementById("notes").value;
  const tags = document.getElementById("tags").value;
  const params = {
    url,
    title,
    description,
    feat_image,
    favicon,
    notes,
    tags,
    updateOrInsert,
  };
  if (bookmarkUUID) {
    params.uuid = bookmarkUUID;
  }
  //console.log(params);
  const formData = new FormData();
  Object.keys(params).forEach((key) => {
    formData.append(key, params[key]);
  });
  axios
    .post(`${base_url}/api/manage`, formData, {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
    })
    .then(function (response) {
      e.target.classList.remove("loading");
      if (response.data) {
        toastMessage("Inserted successfully");
        toastMessage("Refresh to see the changes");
        resetBookmarkModal();
        closeOpenModals();
      }
      if (response.data == -1) {
        toastMessage("Invalid url");
      }
      if (response.data == -2) {
        toastMessage("URL already inserted");
      }
      //console.log(response.data);
    })
    .catch(function (error) {
      console.log(error);
    });
};
//make tag element
const makeTag = (tag) => {
  const elem = document.createElement("div");
  elem.classList.add("tag");
  elem.dataset.tagid = tag.tag_id;
  elem.innerHTML = `
      <!-- tag info -->
      <div class="tag-wrap" data-tagid="${tag.tag_id}">
        <span>#</span>
        <input type="text" value="${tag.tag}" class="input">
        <div class="tag-name">${tag.tag}</div>
      </div>
      <!-- menu -->
      <div class="options btn">
        <img class="option-menu-image" src="${base_url}/assets/icons/more.svg" alt="">
        <div class="menu-options f-light modal ">
          <div class="option edit" data-tagid="${tag.tag_id}" >
            <img src="${base_url}/assets/icons/edit.svg" alt="">
            edit
          </div>
          <div class="option warning" data-tagid="${tag.tag_id}">
            <img src="${base_url}/assets/icons/trash.svg" alt="">
            delete
          </div>
        </div>
      </div>
      <!-- close tag -->
    `;
  //open close tag menu
  elem.querySelector(".options.btn").addEventListener("click", (e) => {
    //closeMenuModals();
    e.stopPropagation();
    //console.log(e.target);
    const menu = e.target.querySelector(".menu-options");
    menu && !menu.classList.contains("open") ? closeMenuModals() : null;
    menu && menu.classList.toggle("open");
  });
  //delete
  elem.querySelector(".menu-options .warning").addEventListener("click", (e) => {
    e.stopPropagation();
    //remove
    const params = {
      id: e.target.dataset.tagid,
      delete_tag: true,
    };
    //console.log(params);
    const formData = new FormData();
    Object.keys(params).forEach((key) => {
      formData.append(key, params[key]);
    });
    axios
      .post(`${base_url}/api/manage`, formData, {
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
      })
      .then(function (response) {
        if (response.data) {
          elem.remove();
          toastMessage("Deleted successfully");
        } else {
          toastMessage("Error, something went wrong:" + response.data);
        }
      })
      .catch(function (error) {
        console.log(error);
      });
  });
  //edit tag
  elem.querySelector(".menu-options .edit").addEventListener("click", (e) => {
    e.stopPropagation();
    closeMenuModals();
    const parent = e.target.closest(".tag");
    const input = parent.querySelector("input");
    parent.classList.toggle("edit");
    input.focus();
    input.addEventListener("change", (e) => {
      e.target.value = e.target.value.trim();
      parent.querySelector(".tag-name").innerText = e.target.value;
    });
    input.addEventListener("focusout", (e) => {
      parent.classList.remove("edit");
      //console.log(parent);
      const params = {
        tagid: parent.dataset.tagid,
        newvalue: e.target.value,
        update_tag: true,
      };
      //console.log(params);
      const formData = new FormData();
      Object.keys(params).forEach((key) => {
        formData.append(key, params[key]);
      });
      axios
        .post(`${base_url}/api/manage`, formData, {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
        })
        .then(function (response) {
          if (response.data.result) {
            toastMessage("tag name changed");
            toastMessage("Refresh to see the changes");
          } else {
            toastMessage("Something went wrong");
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    });
  });
  //return tag elem
  return elem;
};
//populate tags
const populateTags = () => {
  const tagslistelem = document.getElementById("tagslist");
  removeAllChildren(tagslistelem);
  //tagslistelem.addEventListener('click', closeMenuModals);
  //console.log("here");
  if (
    typeof tagsList != "undefined" &&
    typeof tagsList.list != "undefined" &&
    tagsList.list.length
  ) {
    tagsList.list.forEach((tag) => {
      tagslistelem.appendChild(makeTag(tag));
    });
  }
};
//start infinite scroll mode
const infiniteScroll = async (elem = null) => {
  if (!elem) {
    return false;
  }
  /* loader checks auto
  if(needsToFill(elem)){
    await getBookmarks();
  }
  */
  // init Infinte Scroll
  window.addEventListener("scroll", async () => {
    // Do not run if currently fetching
    if (isFetching) return;
    // Scrolled to bottom
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - threshold) {
      await getBookmarks();
    }
  });
};
//get window max height
const windowHeight = () => {
  //window height
  let scrollHeight = Math.max(
    document.body.scrollHeight,
    document.documentElement.scrollHeight,
    document.body.offsetHeight,
    document.documentElement.offsetHeight,
    document.body.clientHeight,
    document.documentElement.clientHeight
  );
  return scrollHeight;
};
//checks if from element needs to load more elements
const needsToFill = (elem = null) => {
  if (!elem) {
    return false;
  }
  let rect = elem.getBoundingClientRect();
  return rect.bottom <= windowHeight();
};
//search for tags suggestions
const searchTagSuggestions = (text, maxResults = 4) => {
  let found = 0;
  if (typeof tagsList.list == "undefined" || !tagsList.list.length) {
    return [];
  }
  return tagsList.list.filter((tag) => {
    //let result = fuzzysearch(text, tag.tag);
    let result = tag.tag.includes(text);
    result ? found++ : null;
    return result && found < maxResults + 1;
  });
};
//fuzzy search compare
const fuzzysearch = (needle, haystack) => {
  var hlen = haystack.length;
  var nlen = needle.length;
  if (nlen > hlen) {
    return false;
  }
  if (nlen === hlen) {
    return needle === haystack;
  }
  outer: for (var i = 0, j = 0; i < nlen; i++) {
    var nch = needle.charCodeAt(i);
    while (j < hlen) {
      if (haystack.charCodeAt(j++) === nch) {
        // eslint-disable-next-line no-labels
        continue outer;
      }
    }
    return false;
  }
  return true;
};
//debounce
function debounce(func, wait, immediate) {
  var timeout;
  return function () {
    var context = this,
      args = arguments;
    var later = function () {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
}
//toast message
const toastMessage = (msg, longer = false, megalonger = false) => {
  let time = longer ? 6000 : 3000;
  if (megalonger) {
    time = 60000;
  }
  const toast = document.getElementById("toastmessage");
  let alertBox = document.createElement("div");
  alertBox.innerText = msg;
  if (toast.children.length > 1) {
    toast.insertBefore(alertBox, toast.childNodes[0]);
    setTimeout(function () {
      toast.removeChild(alertBox);
    }, time);
  } else {
    toast.appendChild(alertBox);
  }
  alertBox.classList.add("slide-in");
  longer ? alertBox.classList.add("longer") : null;
  if (megalonger) {
    alertBox.classList.remove("longer");
    alertBox.classList.add("megalonger");
  }
};
//get media info
const getMediaInfo = debounce(() => {
  document.getElementById("bookmarkmodal").classList.add("loading");
  const params = {
    url: document.getElementById("url").value,
    mediainfo: true,
  };
  const formData = new FormData();
  Object.keys(params).forEach((key) => {
    formData.append(key, params[key]);
  });
  axios
    .post(`${base_url}/api/manage`, formData, {
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
    })
    .then(function (response) {
      document.getElementById("bookmarkmodal").classList.remove("loading");
      //console.log(response.data);
      if (response.data.title) {
        document.getElementById("title").value = response.data.title;
      }
      if (response.data.description) {
        document.getElementById("description").value = response.data.description;
      }
      if (response.data.featured_image) {
        document.getElementById("image").value = response.data.featured_image;
      }
      if (response.data.favicon) {
        let favUrl = response.data.favicon;
        if (!response.data.favicon.includes("http")) {
          favUrl = getHostnameProtocol(document.getElementById("url").value) + favUrl;
          //this should/can be improved
          //favUrl = favUrl.replace("//", "/");
        }
        document.getElementById("favicon").value = favUrl;
      }
      if (response.data.keywords) {
        document.getElementById("tags").value = response.data.keywords
          .replace(/, /g, ",")
          .replace(/ /g, "-")
          .replace(/#/g, "");
      }
    })
    .catch(function (error) {
      console.log(error);
    });
}, 600);
