class Container {
  //constructor
  constructor(
    container,
    initialState,
    lang = {
      delete: "Delete",
      edit: "Edit",
      preview: "Preview",
      page: "Page %s",
      toplevel: "Top Level",
      storylines: "Storylines",
      pages: "Pages",
      addstoryline: "Add a storyline here",
      rearrange: "Rearrange these storylines"
    }
  ) {
    let stateDefaults = {
      heading: "",
      root: "",
      ccroot: "",
      direction: "none",
      rows: [],
      pagenum: 1,
      numpages: 1,
    };
    this.container = container;
    this.state = Object.assign(stateDefaults, initialState);
    this.lang = lang;
    this.getPage({});
  }

  //container building function
  buildContainer() {
    let rows = this.formatRows();

    let container = $(
      `<div class="slideover"><div class="col-header blue-bg">${this.state.heading}</div><div class="row-container">${rows}</div></div>`
    );

    return container;
  }

  //placeholder for subclasses
  formatRows(rows = this.state.rows) {
    let gray = true;
    let formatted = rows.map((row) => {
      if (gray) gray = false;
      else gray = true;
      let rowContent = this.makeRow(row);
      let insertRow = $(
        `<div class="zebra-row${gray ? " gray-bg" : ""
        }"><div style="clear:both"></div></div>`
      );
      insertRow.prepend(rowContent);
      return insertRow;
    });
    return formatted;
  }

  makeRow(row) {
    return "";
  }

  //change rows in container function
  updateContainer(data) {
    this.state = Object.assign(this.state, JSON.parse(data));

    let newdiv = this.buildContainer();

    if (this.state.direction == "none") {
      this.container.html(newdiv);
    } else {
      let olddiv = this.container.find(".slideover");
      this.container.css("height", this.container.height());
      olddiv.css("position", "absolute");

      newdiv.css("position", "absolute");
      if (this.state.direction == "right") newdiv.css("left", "100%");
      else newdiv.css("left", "-100%");
      this.container.append(newdiv);
      let oldleft = "100%";
      if (this.state.direction == "right") oldleft = "-100%";
      this.container.animate({ height: newdiv.height() }, { duration: 200 });
      $(function () {
        olddiv.animate(
          {
            left: oldleft,
          },
          { duration: 200, queue: false }
        );

        newdiv.animate(
          {
            left: 0,
          },
          { duration: 200, queue: false }
        );
      });
    }
  }

  //handle clicks for AJAX response with state object
  getPage(newData) {
    this.state = Object.assign(this.state, newData);
    let sendData = Object.assign(this.state, { rows: [] });
    console.log(sendData);
    $.post(this.state.root + this.state.ccroot + "ajax/get-page.php", sendData, (data) =>
      this.updateContainer(data)
    );
  }
}

class StorylineContainer extends Container {
  //constructor
  constructor(container, initialState, lang) {
    super(container, initialState, lang);
    this.state.storylineRows = [];
    this.state.heading = this.lang.toplevel;
    this.state.action = "storyline";
    this.state.storyline = 0;
    this.state.parent = 0;
    this.getPage({});
  }

  //container building function
  buildContainer() {
    if (this.state.heading == "") this.state.heading = this.lang.toplevel;
    let arrow = "";
    if (this.state.storyline != 0) {
      arrow = $(`<div class="back-arrow"><i class="fa fa-caret-left"></i></div><div class="arrow-bump"></div>`);
      arrow.click(() => {
        this.getPage({
          storyline: this.state.parent,
          action: "storyline",
          direction: "left",
        })
      }
      );
    }
    let container = $(`<div class="slideover"><div class="col-header blue-bg">${this.state.heading
      }</div><div class="row-container row-container-storylines"><div class="col-header blue-bg">${this.lang.storylines
      }</div></div><div class="row-container row-container-pages"><div class="col-header blue-bg">${this.lang.pages
      }</div></div></div>`);

    let rowContainer = container.find(".row-container-storylines").first();
    this.formatStorylineRows().map((row) => {
      rowContainer.append(row);
    });
    let storylineButtons = this.addStorylineButtons();
    rowContainer.append(storylineButtons);

    rowContainer = container.find(".row-container-pages").first();
    this.formatRows().map((row) => {
      rowContainer.append(row);
    });

    container.children(".col-header").first().append(arrow);

    return container;
  }

  addStorylineButtons() {
    return $(`<button class="cc-btn light-bg addstoryline" style="margin:0; box-sizing:border-box" onclick="window.location.href='/${this.state.ccroot}modules/${this.state.moduleSlug}/add-storyline/${this.state.storyline}'">${this.lang.addstoryline}</button>
  <button class="cc-btn blue-bg rearrangestorylines" style="margin:0; box-sizing:border-box" onclick="window.location.href='/${this.state.ccroot}modules/${this.state.moduleSlug}/rearrange-storylines/${this.state.storyline}'">Rearrange these storylines</button>`);
  }

  formatStorylineRows() {
    let gray = true;
    let formatted = this.state.storylineRows.map((row) => {
      if (gray) gray = false;
      else gray = true;
      let buttons = this.formatStorylineButtons(row);
      let insertRow = $(
        `<div class="zebra-row${gray ? " gray-bg" : ""
        }"><div class="row-title">${row.name
        }</div><div class="row-buttons">${buttons}</div><div style="clear:both"></div></div>`
      );
      insertRow.click(() => {
        this.getPage({
          storyline: row.id,
          action: this.state.action,
          direction: "right",
        })
      }
      );
      return insertRow;
    });
    return formatted;
  }

  makeRow(row) {
    let buttons = this.formatButtons(row);
    return $(`<div class="row-title">${row.title}</div><div class="row-buttons">${buttons}</div>`);
  }

  //button generating function
  formatButtons(row) {
    let baseLink = `${this.state.root}${this.state.ccroot}modules/${this.state.moduleSlug}/`;
    return `<a href="${baseLink}delete-post/${row.slug}">${this.lang.delete}</a><a href="${baseLink}edit-post/${row.slug}">${this.lang.edit}</a><a href="${this.state.root}${this.state.moduleSlug}/${row.slug}" target="_blank">${this.lang.preview}</a>`;
  }

  formatStorylineButtons(row) {
    let baseLink = `${this.state.root}${this.state.ccroot}modules/${this.state.moduleSlug}/`;
    return `<a class="next-arrow"><i class="fa fa-caret-right"></i></a><a href="${baseLink}delete-storyline/${row.id}">${this.lang.delete}</a><a href="${baseLink}edit-storyline/${row.id}">${this.lang.edit}</a>`;
  }
}

class PageContainer extends Container {
  //constructor
  //super
  constructor(container, initialState, lang) {
    super(container, initialState, lang);
  }

  //add navigation arrows to container function
  buildContainer() {
    let rows = this.formatRows();

    let container = $(
      `<div class="slideover"><div class="col-header blue-bg">${this.state.heading.replace(
        "%s",
        this.state.pagenum
      )}</div><div class="row-container"></div></div>`
    );
    let rowContainer = container.find(".row-container").first();
    rows.map((row) => {
      rowContainer.append(row);
    });

    container
      .children(".col-header")
      .first()
      .append(this.getBackArrow(), this.getNextArrow());

    return container;
  }

  getBackArrow() {
    let backarrow = "";
    if (this.state.pagenum != 1) {
      backarrow = $(
        `<div class="prev-page"><i class="fa fa-caret-left"></i></div><div class="arrow-bump"></div>`
      );
      backarrow.click(() => {
        this.getPage({
          pagenum: this.state.pagenum - 1,
          action: this.state.action,
          direction: "left",
        });
      });
    }
    return backarrow;
  }

  getNextArrow() {
    let nextarrow = "";
    if (this.state.pagenum != this.state.numpages) {
      nextarrow = $(
        `<div class="next-page"><i class="fa fa-caret-right"></i></div>`
      );
      nextarrow.click(() => {
        this.getPage({
          pagenum: this.state.pagenum + 1,
          action: this.state.action,
          direction: "right",
        });
      });
    }
    return nextarrow;
  }
}

class BlogContainer extends PageContainer {
  //constructor
  //super
  constructor(container, initialState, lang) {
    super(container, initialState, lang);
  }

  //button generating function
  formatButtons(row) {
    let baseLink = `${this.state.root}${this.state.ccroot}modules/${this.state.moduleSlug}/`;
    return `<a href="${baseLink}delete-post/${row.slug}">${this.lang.delete}</a><a href="${baseLink}edit-post/${row.slug}">${this.lang.edit}</a><a href="${this.state.root}${this.state.moduleSlug}/${row.slug}" target="_blank">${this.lang.preview}</a>`;
  }
  //row generating function

  makeRow(row) {
    let buttons = this.formatButtons(row);
    return $(`<div class="row-title">${row.title}</div><div class="row-buttons">${buttons}</div>`);
  }
}

class GalleryContainer extends PageContainer {
  //constructor
  //super
  constructor(container, initialState, lang) {
    super(container, initialState, lang);
  }

  //button generating function
  formatButtons(row) {
    let baseLink = `${this.state.root}${this.state.ccroot}modules/${this.state.moduleSlug}/`;
    return `<a href="${baseLink}delete-image/${row.id}">${this.lang.delete}</a><a href="${baseLink}edit-image/${row.id}">${this.lang.edit}</a>`;
  }
  //row generating function

  makeRow(row) {
    let buttons = this.formatButtons(row);
    return $(`<div class="row-img"><img src="${this.state.root}uploads/${row.thumbname}"></div><div class="row-caption">${row.caption}</div><div class="row-buttons">${buttons}</div>`);
  }
}

class MediaContainer extends PageContainer {
  //constructor
  //super
  constructor(container, initialState, lang) {
    super(container, initialState, lang);
    this.getPage({});
  }

  //button generating function
  formatButtons(row) {
    return `<a href="/${this.state.ccroot}image-library/delete-image/${row.id}">${this.lang.delete}</a>`;
  }
  //row generating function

  makeRow(row) {
    let buttons = this.formatButtons(row);
    return $(`<div class="row-img"><img src="${this.state.root}uploads/${row.thumbname}"></div><div class="row-caption"><a href="${this.state.root}uploads/${row.imgname}" target="_blank">${this.state.root}uploads/${row.imgname}</a></div><div class="row-buttons">${buttons}</div>`);
  }
}
