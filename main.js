function _instanceof(left, right) {
  if (
    right != null &&
    typeof Symbol !== "undefined" &&
    right[Symbol.hasInstance]
  ) {
    return !!right[Symbol.hasInstance](left);
  } else {
    return left instanceof right;
  }
}

function _classCallCheck(instance, Constructor) {
  if (!_instanceof(instance, Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function htmlEntities(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

var NodeLinks = /*#__PURE__*/ (function () {
  "use strict";

  function NodeLinks(elLinks) {
    _classCallCheck(this, NodeLinks);

    // is APIobj["links"]
    this.elLinks = elLinks;
    this.lookup = {
      documents: "Documenti",
      transactions: "Contabilit&agrave;",
      questions: "Domande",
      events: "Eventi",
      feedbacks: "Feedbacks"
    };
  }

  _createClass(NodeLinks, [
    {
      key: "computeHTML",
      value: function computeHTML() {
        var _this = this;

        if (!this.elLinks) return "";
        this.html = '<div class="linkList">';
        var flag = false,
          list = [];
        var dict = null;
        if (Object.keys(this.elLinks).includes("to")) dict = this.elLinks["to"];

        if (dict != null) {
          var ks = Object.keys(dict);

          if (ks.length > 0 && !flag) {
            this.html +=
              '<span>Risorse collegate:</span><div data-role="collapsibleset" data-theme="a" data-content-theme="a">';
            flag = true;
          }

          ks.forEach(function (category) {
            list = dict[category];
            _this.html +=
              '<div data-role="collapsible"><h3>' +
              _this.lookup[category] +
              "</h3>";
            list.forEach(function (obj) {
              _this.html +=
                '<p><button type="button" onclick="location.href=\'api/view/UID/' +
                obj["UID"] +
                "/render';\">" +
                htmlEntities(obj["title"]) +
                "</button></p>";
            });
            _this.html += "</div>";
          });

          if (ks.length > 0 && !flag) {
            this.html += "</div>";
          }
        }

        flag = false;
        dict = this.elLinks["from"];

        if (dict != null) {
          ks = Object.keys(dict);

          if (ks.length > 0 && !flag) {
            this.html +=
              '<span>Fanno riferimento a questa risorsa:</span><div data-role="collapsibleset" data-theme="a" data-content-theme="a">';
            flag = true;
          }

          ks.forEach(function (category) {
            list = dict[category];
            _this.html +=
              '<div data-role="collapsible"><h3>' +
              _this.lookup[category] +
              "</h3>";
            list.forEach(function (obj) {
              _this.html +=
                '<p><button type="button" onclick="location.href=\'api/view/UID/' +
                obj["UID"] +
                "/render';\">" +
                htmlEntities(obj["title"]) +
                "</a></p>";
            });
            _this.html += "</div>";
          });

          if (ks.length > 0 && !flag) {
            this.html += "</div>";
          }
        }

        this.html += "</div>";
        return this.html;
      }
    }
  ]);

  return NodeLinks;
})();

var Tags = /*#__PURE__*/ (function () {
  "use strict";

  function Tags(tags) {
    _classCallCheck(this, Tags);

    this.tags = tags;
  }

  _createClass(Tags, [
    {
      key: "computeHTML",
      value: function computeHTML() {
        var _this2 = this;

        if (this.tags != null && this.tags.length > 0) {
          this.html += '<div data-role="collapsible"><h3>Tag</h3>';
          this.tags.forEach(function (element) {
            _this2.html +=
              '<p><a href="api/view/tags/' +
              encodeURI(element) +
              '/render">' +
              htmlEntities(element) +
              "</a></p>";
          });
        }
      }
    }
  ]);

  return Tags;
})();

function eurSet(eur) {}

var ElementPreviewList = /*#__PURE__*/ (function () {
  "use strict";

  function ElementPreviewList() {
    _classCallCheck(this, ElementPreviewList);

    this.k2it = {
      planned: "Previsto il",
      executed: "Effettuato il",
      amount: "Importo",
      title: "Titolo",
      shortDescription: "Descrizione breve",
      htmlDescription: "Testo in html",
      createdDate: "Data di creazione",
      publishedDate: "Data di pubblicazione",
      lastEditedDate: "Data di ultima modifica",
      thumbnailUrl: "Url anteprima",
      documentUrl: "Url documento",
      startDate: "Data inizio",
      endDate: "Data fine",
      participants: "Partecipanti",
      isAssembly: "Tipo",
      publishResultsDate: "Data pubblicazione dei risultati",
      questionTitle: "Titolo domanda",
      questionText: "Domanda",
      answerTitle: "Titolo risposta",
      answerText: "Risposta",
      askDate: "Data richiesta",
      answerDate: "Data risposta",
      asker: "Richiedente",
      replier: "Rispondente"
    };
  }

  _createClass(ElementPreviewList, [
    {
      key: "itLookup",
      value: function itLookup(key) {
        return this.k2it[key] ? this.k2it[key] : key;
      }
    },
    {
      key: "itLookupPairDict",
      value: function itLookupPairDict(dict, key) {
        return {
          value: dict[key] ? dict[key] : "",
          name: this.itLookup(key)
        };
      }
    },
    {
      key: "textGen",
      value: function textGen(title, value) {
        if (value && title) {
          return (
            "<span><h3>" +
            htmlEntities(title) +
            "</h3><br/><p>" +
            htmlEntities(value) +
            "</p></span>"
          );
        }

        return "";
      }
    },
    {
      key: "listgen",
      value: function listgen(title) {
        var values =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : [];
        var colors =
          arguments.length > 2 && arguments[2] !== undefined
            ? arguments[2]
            : [];
        var uids =
          arguments.length > 3 && arguments[3] !== undefined
            ? arguments[3]
            : [];
        var html = "";

        if (title && values.length > 0) {
          html += "<h3>" + htmlEntities(title) + "</h3><br />";

          for (var i = 0; i < values.length; i++) {
            if (!values[i]) continue;
            var color = colors[i] ? colors[i] : colors[0] ? colors[0] : "white";
            var a = "<span>";
            if (uids[i])
              a =
                '<span style="text-decoration: underline;" onclick="location.href=\'api/view/UID/' +
                uids[i] +
                "';\">";
            html +=
              a +
              '<span class="w3-tag w3-' +
              color +
              '">' +
              htmlEntities(values[i]) +
              "</span></span>";
          }

          return html;
        }
      }
    },
    {
      key: "conditonalTextGen",
      value: function conditonalTextGen(title, bool, yes) {
        var no =
          arguments.length > 3 && arguments[3] !== undefined
            ? arguments[3]
            : "";
        return this.textGen(title, bool ? yes : no);
      }
    },
    {
      key: "boolGen",
      value: function boolGen(title, value) {
        var res = value ? "S&igrave;" : "No";
        var color = value ? "green" : "red";
        return this.listgen(title, [res], [color], []);
      }
    },
    {
      key: "thumbnailGen",
      value: function thumbnailGen(url) {
        var MAXheight =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : 200;
        var MAXwidth =
          arguments.length > 2 && arguments[2] !== undefined
            ? arguments[2]
            : 200;
        var alt =
          arguments.length > 3 && arguments[3] !== undefined
            ? arguments[3]
            : "thumbnail";
        return (
          '<img src="' +
          url +
          '" onclick="location.href=\'' +
          url +
          '\';" style="max-width:' +
          MAXwidth +
          "px;max-heght:" +
          MAXheight +
          'px;" alt="' +
          htmlEntities(alt) +
          '" />'
        );
      }
    },
    {
      key: "tagsgen",
      value: function tagsgen(title) {
        var values =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : [];
        var colors =
          arguments.length > 2 && arguments[2] !== undefined
            ? arguments[2]
            : [];
        var html = "";

        if (title && values.length > 0) {
          html += "<h3>" + htmlEntities(title) + "</h3><br />";

          for (var i = 0; i < values.length; i++) {
            if (!values[i]) continue;
            var color = colors[i] ? colors[i] : colors[0] ? colors[0] : "black";
            var a =
              '<span style="text-decoration: underline;" onclick="location.href=\'api/view/tags/' +
              values[i] +
              "/detailed/render';\">";
            html +=
              a +
              '<span class="w3-tag w3-' +
              color +
              '">' +
              htmlEntities(values[i]) +
              "</span></span>";
          }

          return html;
        }
      }
    },
    {
      key: "generateLi",
      value: function generateLi(type, array) {
        var _this3 = this;

        var tLihtml = "",
          t;

        switch (type) {
          case "document":
            array.forEach(function (element) {
              tLihtml +=
                '<li>\n<a href="api/view/UID/' + element["UID"] + '/render">';
              tLihtml +=
                '<h1 style="font-size:30px;">' +
                htmlEntities(element["title"]) +
                "</h1>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " +
                _this3.thumbnailGen(
                  element["thumbnailUrl"],
                  200,
                  200,
                  element["title"]
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<h4>" + htmlEntities(element["shortDescription"]) + "</h4>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span>" +
                _this3.listgen(
                  "Formato",
                  [element["format"]["type"], element["format"]["description"]],
                  [element["format"]["uiColor"]]
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "publishedDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span>" + _this3.tagsgen("Tag", element["tags"]) + "</span>";
              tLihtml += "<br /><hr />";
              var nl = new NodeLinks(element["links"]);
              tLihtml += nl.computeHTML();
              tLihtml += "</a></li>";
            });
            break;

          case "event":
            array.forEach(function (element) {
              tLihtml +=
                '<li>\n<a href="api/view/UID/' + element["UID"] + '/render">';
              tLihtml +=
                '<h1 style="font-size:30px;">' +
                htmlEntities(element["title"]) +
                "</h1>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " +
                _this3.thumbnailGen(
                  element["thumbnailUrl"],
                  200,
                  200,
                  element["title"]
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<h4>" + htmlEntities(element["shortDescription"]) + "</h4>";
              tLihtml += "<br /><hr />";
              tLihtml += _this3.conditonalTextGen(
                " ",
                element["isAssembly"],
                "È un assemblea"
              );
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "startDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "endDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " +
                _this3.listgen("Partecipanti", element["participants"]) +
                "</span>";
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "publishedDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " + _this3.tagsgen("Tag", element["tags"]) + "</span>";
              tLihtml += "<br /><hr />";
              var nl = new NodeLinks(element["links"]);
              tLihtml += nl.computeHTML();
              tLihtml += "</a></li>";
            });
            break;

          case "feedback":
            array.forEach(function (element) {
              tLihtml +=
                '<li>\n<a href="api/view/UID/' + element["UID"] + '/render">';
              tLihtml +=
                '<h1 style="font-size:30px;">' +
                htmlEntities(element["title"]) +
                "</h1>";
              tLihtml += "<br /><hr />";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " +
                _this3.thumbnailGen(
                  element["thumbnailUrl"],
                  200,
                  200,
                  element["title"]
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<h4>" + htmlEntities(element["shortDescription"]) + "</h4>";
              tLihtml += "<br /><hr />";
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "startDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "endDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "publishResultsDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";

              _this3.itLookupPairDict(element, "publishedDate");

              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " + _this3.tagsgen("Tag", element["tags"]) + "</span>";
              var nl = new NodeLinks(element["links"]);
              tLihtml += nl.computeHTML();
              tLihtml += "</a></li>";
            });
            break;

          case "question":
            array.forEach(function (element) {
              tLihtml +=
                '<li>\n<a href="api/view/UID/' + element["UID"] + '/render">';
              tLihtml +=
                '<h1 style="font-size:30px;">' +
                htmlEntities(element["questionTitle"]) +
                "</h1>";
              tLihtml += "<br /><hr />";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " +
                _this3.thumbnailGen(
                  element["thumbnailUrl"],
                  200,
                  200,
                  element["title"]
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              t = _this3.itLookupPairDict(element, "publishedDate");
              tLihtml +=
                "<span> " +
                _this3.textGen(
                  t["name"],
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</span>";
              tLihtml += "<br /><hr />";
              tLihtml +=
                "<span> " + _this3.tagsgen("Tag", element["tags"]) + "</span>";
              var nl = new NodeLinks(element["links"]);
              tLihtml += nl.computeHTML();
              tLihtml += "</a></li>";
            });
            break;

          case "transaction":
            array.forEach(function (element) {
              tLihtml += "<tr>";
              tLihtml +=
                "<td> " +
                _this3.thumbnailGen(
                  element["thumbnailUrl"],
                  200,
                  200,
                  element["title"]
                ) +
                "</td>";
              t = _this3.itLookupPairDict(element, "title");
              tLihtml += "<td> " + _this3.textGen(" ", t["value"]) + "</td>";
              t = _this3.itLookupPairDict(element, "planned");
              tLihtml +=
                "<td> " +
                _this3.textGen(
                  " ",
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</td>";
              t = _this3.itLookupPairDict(element, "executed");
              tLihtml +=
                "<td> " +
                _this3.textGen(
                  " ",
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</td>";
              tLihtml += "<td>" + eurS(element["amount"]) + "</td>";
              tLihtml += "<td> " + _this3.textGen(" ", t["value"]) + "</td>";
              t = _this3.itLookupPairDict(element, "shortDescription");
              tLihtml += "<td> " + _this3.textGen(" ", t["value"]) + "</td>";
              t = _this3.itLookupPairDict(element, "publishedDate");
              tLihtml +=
                "<td> " +
                _this3.textGen(
                  " ",
                  new TimeFormatter(t["value"]).compute("LLLL")
                ) +
                "</td>";
              tLihtml +=
                "<td> " + _this3.tagsgen(" ", element["tags"]) + "</td>";
              tLihtml +=
                '<td><a href="api/view/UID/' +
                element["UID"] +
                '/render">link</a></td>';
              tLihtml + "<td></td>";
              /*var nl=new NodeLinks(element["links"]);
            tLihtml+= nl.computeHTML();*/

              tLihtml += "</tr>";
            });
            break;
        }

        return tLihtml;
      }
    }
  ]);

  return ElementPreviewList;
})();

var TimeFormatter = /*#__PURE__*/ (function () {
  "use strict";

  function TimeFormatter() {
    var timeString =
      arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

    _classCallCheck(this, TimeFormatter);

    if (timeString) this.m = moment(timeString);
    else this.m = moment();
    if (this.m.locale() != "it") this.m.locale("it", null);
  }

  _createClass(TimeFormatter, [
    {
      key: "compute",
      value: function compute(format) {
        return this.m.format(format);
      }
    }
  ]);

  return TimeFormatter;
})();

var APIfetch = /*#__PURE__*/ (function () {
  "use strict";

  function APIfetch(epl) {
    _classCallCheck(this, APIfetch);

    this.epl = epl;
  }

  _createClass(APIfetch, [
    {
      key: "fetchDocs",
      value: function fetchDocs() {
        var start =
          arguments.length > 0 && arguments[0] !== undefined
            ? arguments[0]
            : false;
        var end =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : false;
        $.post(
          "api/view/documents/all/",
          {
            start: start,
            end: end
          },
          function (data) {
            this.renderDocs(data);
          }.bind(this),
          "json"
        );
      }
    },
    {
      key: "fetchEvents",
      value: function fetchEvents() {
        var start =
          arguments.length > 0 && arguments[0] !== undefined
            ? arguments[0]
            : false;
        var end =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : false;
        $.post(
          "api/view/events/all/",
          {
            start: start,
            end: end
          },
          function (data) {
            this.renderEvents(data);
          }.bind(this),
          "json"
        );
      }
    },
    {
      key: "fetchFeedbacks",
      value: function fetchFeedbacks() {
        var start =
          arguments.length > 0 && arguments[0] !== undefined
            ? arguments[0]
            : false;
        var end =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : false;
        $.post(
          "api/view/feedbacks/all/",
          {
            start: start,
            end: end
          },
          function (data) {
            this.renderFeedbacks(data);
          }.bind(this),
          "json"
        );
      }
    },
    {
      key: "fetchQuestions",
      value: function fetchQuestions() {
        var start =
          arguments.length > 0 && arguments[0] !== undefined
            ? arguments[0]
            : false;
        var end =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : false;
        $.post(
          "api/view/questions/all/",
          {
            start: start,
            end: end
          },
          function (data) {
            this.renderQuestions(data);
          }.bind(this),
          "json"
        );
      }
    },
    {
      key: "fetchTransactions",
      value: function fetchTransactions() {
        var start =
          arguments.length > 0 && arguments[0] !== undefined
            ? arguments[0]
            : false;
        var end =
          arguments.length > 1 && arguments[1] !== undefined
            ? arguments[1]
            : false;
        $.post(
          "api/view/transactions/all/",
          {
            start: start,
            end: end
          },
          function (data) {
            this.renderTransactions(data);
          }.bind(this),
          "json"
        );
      }
    },
    {
      key: "renderTransactions",
      value: function renderTransactions(data) {
        var a = new ElementPreviewList().generateLi(
          "transaction",
          data["data"]
        );
        console.log(a);
        $("#accountingList").html(a);
        $("#accountingList").enhanceWithin();
        $("#accountingList").listview("refresh");
      }
    },
    {
      key: "renderFeedbacks",
      value: function renderFeedbacks(data) {
        var a = new ElementPreviewList().generateLi("feedback", data["data"]);
        $("#feedbacksList").html(a);
        $("#feedbacksList").enhanceWithin();
        $("#feedbacksList").listview("refresh");
      }
    },
    {
      key: "renderQuestions",
      value: function renderQuestions(data) {
        var a = new ElementPreviewList().generateLi("question", data["data"]);
        $("#questionsList").html(a);
        $("#questionsList").enhanceWithin();
        $("#questionsList").listview("refresh");
      }
    },
    {
      key: "renderDocs",
      value: function renderDocs(data) {
        var a = new ElementPreviewList().generateLi("document", data["data"]);
        $("#documentsList").html(a);
        $("#documentsList").enhanceWithin();
        $("#documentsList").listview("refresh");
      }
    },
    {
      key: "renderEvents",
      value: function renderEvents(data) {
        var a = new ElementPreviewList().generateLi("event", data["data"]);
        $("#eventsList").html(a);
        $("#eventsList").enhanceWithin();
        $("#eventsList").listview("refresh");
      }
    }
  ]);

  return APIfetch;
})();

function eurS(val) {
  if (val == 0)
    return (
      '<h2 style="color:yellow">&euro;' +
      formatMoney(val, 2, ",", "'") +
      "</h2>"
    );
  if (val < 0)
    return (
      '<h2 style="color:red">&euro;' + formatMoney(val, 2, ",", "'") + "</h2>"
    );
  return (
    '<h2 style="color:green">&euro;' + formatMoney(val, 2, ",", "'") + "</h2>"
  );
}

function formatMoney(number, decPlaces, decSep, thouSep) {
  (decPlaces = isNaN((decPlaces = Math.abs(decPlaces))) ? 2 : decPlaces),
    (decSep = typeof decSep === "undefined" ? "." : decSep);
  thouSep = typeof thouSep === "undefined" ? "," : thouSep;
  var sign = number < 0 ? "-" : "";
  var i = String(
    parseInt((number = Math.abs(Number(number) || 0).toFixed(decPlaces)))
  );
  var j = (j = i.length) > 3 ? j % 3 : 0;
  return (
    sign +
    (j ? i.substr(0, j) + thouSep : "") +
    i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
    (decPlaces
      ? decSep +
        Math.abs(number - i)
          .toFixed(decPlaces)
          .slice(2)
      : "")
  );
}

function searchE(listArr, key) {
  for (var i = 0; i < listArr.length; i++) {
    var e = listArr[i];
    if (e["name"] == key) return e["value"];
  }
}

var epl = new ElementPreviewList();
var api = new APIfetch(epl);
api.fetchDocs();
api.fetchEvents();
api.fetchFeedbacks();
api.fetchQuestions();
api.fetchTransactions();
var start, end;
$(document).delegate("form[data-act=search]", "submit", function () {
  var data = $(this).serializeArray();
  start = searchE(data, "start") ? searchE(data, "start") : "false";
  end = searchE(data, "end") ? searchE(data, "start") : "false";

  switch (searchE(data, "search")) {
    case "events":
      api.fetchEvents(start, end);
      break;

    case "documents":
      api.fetchDocs(start, end);
      break;

    case "feedbacks":
      api.fetchFeedbacks(start, end);
      break;

    case "questions":
      api.fetchQuestions(start, end);
      break;

    case "transactions":
      api.fetchTransactions(start, end);
      break;
  }

  return false;
});

function onKonamiCode(cb) {
  var input = "";
  var key = "38384040373937396665";
  document.addEventListener("keydown", function (e) {
    input += "" + e.keyCode;

    if (input === key) {
      return cb();
    }

    if (!key.indexOf(input)) return;
    input = "" + e.keyCode;
  });
}

onKonamiCode(function () {
  window.open("https://www.youtube.com/watch?v=9YG9INjO91Y", "_blank");
});