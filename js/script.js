!function () {
  var today = moment();

  function Calendar(selector, events) {
    this.el = document.querySelector(selector);
    this.events = events;
    this.current = moment().date(1);
    this.draw();
    var current = document.querySelector('.today');
    if (current) {
      var self = this;
      window.setTimeout(function () {
        self.openDay(current);
      }, 500);
    }
  }

  Calendar.prototype.draw = function () {
    this.drawHeader();
    this.drawMonth();
    this.drawLegend();
  }

  Calendar.prototype.drawHeader = function () {
    var self = this;
    if (!this.header) {
      //Create the header elements
      this.header = createElement('div', 'header');
      this.header.className = 'header';

      this.title = createElement('h1');

      var right = createElement('div', 'right');
      right.addEventListener('click', function () { self.nextMonth(); });

      var left = createElement('div', 'left');
      left.addEventListener('click', function () { self.prevMonth(); });
      //Append the Elements
      this.header.appendChild(this.title);
      this.header.appendChild(right);
      this.header.appendChild(left);
      this.el.appendChild(this.header);
    }

    this.title.innerHTML = this.current.format('MMMM YYYY');
  }

  Calendar.prototype.drawMonth = function () {
    var self = this;

    if (this.events !== null) {
      this.events.forEach(function (ev) {
        ev.date = moment(ev.date);
      });
    }

    if (this.month) {
      this.oldMonth = this.month;
      this.oldMonth.className = 'month out ' + (self.next ? 'next' : 'prev');
      this.oldMonth.addEventListener('webkitAnimationEnd', function () {
        self.oldMonth.parentNode.removeChild(self.oldMonth);
        self.month = createElement('div', 'month');
        self.backFill();
        self.currentMonth();
        self.fowardFill();
        self.el.appendChild(self.month);
        window.setTimeout(function () {
          self.month.className = 'month in ' + (self.next ? 'next' : 'prev');
        }, 16);
      });
    } else {
      this.month = createElement('div', 'month');
      this.el.appendChild(this.month);
      this.backFill();
      this.currentMonth();
      this.fowardFill();
      this.month.className = 'month new';
    }
  }

  Calendar.prototype.backFill = function () {
    var clone = this.current.clone();
    var dayOfWeek = clone.day();

    if (!dayOfWeek) { return; }

    clone.subtract('days', dayOfWeek + 1);

    for (var i = dayOfWeek; i > 0; i--) {
      this.drawDay(clone.add('days', 1));
    }
  }

  Calendar.prototype.fowardFill = function () {
    var clone = this.current.clone().add('months', 1).subtract('days', 1);
    var dayOfWeek = clone.day();

    if (dayOfWeek === 6) { return; }

    for (var i = dayOfWeek; i < 6; i++) {
      this.drawDay(clone.add('days', 1));
    }
  }

  Calendar.prototype.currentMonth = function () {
    var clone = this.current.clone();

    while (clone.month() === this.current.month()) {
      this.drawDay(clone);
      clone.add('days', 1);
    }
  }

  Calendar.prototype.getWeek = function (day) {
    if (!this.week || day.day() === 0) {
      this.week = createElement('div', 'week');
      this.month.appendChild(this.week);
    }
  }

  Calendar.prototype.drawDay = function (day) {
    var self = this;
    this.getWeek(day);

    //Outer Day
    var outer = createElement('div', this.getDayClass(day));
    outer.addEventListener('click', function () {
      $(".day").siblings().removeClass("activeday");
      $(this).addClass("activeday");
      $(this).siblings().removeClass("activeday");

      self.openDay(this);

    });

    //Day Name
    var name = createElement('div', 'day-name', day.format('ddd'));
    //Day Number
    var number = createElement('div', 'day-number', day.format('DD'));
    //Events
    var events = createElement('div', 'day-events');
    this.drawEvents(day, events);

    outer.appendChild(name);
    outer.appendChild(number);
    outer.appendChild(events);
    this.week.appendChild(outer);
  }

  Calendar.prototype.drawEvents = function (day, element) {
    if (day.month() === this.current.month()) {
      if (this.events !== null) {
        var todaysEvents = this.events.reduce(function (memo, ev) {
          ev.date = moment(ev.date);
          if (ev.date.isSame(day, 'day')) {
            memo.push(ev);
          }
          return memo;
        }, []);

        todaysEvents.forEach(function (ev) {
          // 判斷有無',' 有的話印出所有分類顏色
          if (ev.statusCategory.includes(',') == true) {
            let statusCategorySplit = ev.statusCategory.split(',');
            for (let i = 0; i < statusCategorySplit.length; i++) {
              switch (statusCategorySplit[i]) {
                case 'unCheckIn':
                  var unCheckInSpan = createElement('span', statusCategorySplit[i]);
                  element.appendChild(unCheckInSpan);
                  break;
                case 'ontime':
                  var ontimeSpan = createElement('span', statusCategorySplit[i]);
                  element.appendChild(ontimeSpan);
                  break;
                case 'late':
                  var lateSpan = createElement('span', statusCategorySplit[i]);
                  element.appendChild(lateSpan);
                  break;
                case 'excused':
                  var excusedSpan = createElement('span', statusCategorySplit[i]);
                  element.appendChild(excusedSpan);
                  break;
              }
            }
          } else {
            var evSpan = createElement('span', ev.statusCategory);
            element.appendChild(evSpan);
          }
        });
      }
    }
  }

  Calendar.prototype.getDayClass = function (day) {
    classes = ['day'];
    if (day.month() !== this.current.month()) {
      classes.push('other');
    } else if (today.isSame(day, 'day')) {
      classes.push('today');
    }
    return classes.join(' ');
  }

  Calendar.prototype.openDay = function (el) {
    var details, arrow;
    var dayNumber = +el.querySelectorAll('.day-number')[0].innerText || +el.querySelectorAll('.day-number')[0].textContent;
    var day = this.current.clone().date(dayNumber);

    var currentOpened = document.querySelector('.details');

    //Check to see if there is an open detais box on the current row
    if (currentOpened && currentOpened.parentNode === el.parentNode) {
      details = currentOpened;
      arrow = document.querySelector('.arrow');

    } else {
      //Close the open events on differnt week row
      if (currentOpened) {
        currentOpened.addEventListener('webkitAnimationEnd', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('oanimationend', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('msAnimationEnd', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.addEventListener('animationend', function () {
          currentOpened.parentNode.removeChild(currentOpened);
        });
        currentOpened.className = 'details out';
        removeElementsByClass('details in');
      }

      //Create the Details Container
      details = createElement('div', 'details in');
      //Create the arrow
      var arrow = createElement('div', 'arrow');
      //Create the event wrapper
      details.appendChild(arrow);
      el.parentNode.appendChild(details);
    }

    if (this.events !== null) {
      var todaysEvents = this.events.reduce(function (memo, ev) {
        if (ev.date.isSame(day, 'day')) {
          memo.push(ev);
        }
        return memo;
      }, []);
      this.renderEvents(todaysEvents, details);
    } else {
      this.renderEvents(this.events, details);
    }
    let detailsW = parseInt((el.parentNode.offsetWidth / 7) / 2.1);
    arrow.style.left = el.offsetLeft + detailsW + 'px';
  }

  Calendar.prototype.renderEvents = function (events, ele) {
    //Remove any events in the current details element
    var currentWrapper = ele.querySelector('.events');
    var wrapper = createElement('div', 'events in' + (currentWrapper ? ' new' : ''));
    if (events == null) {
      removeElementsByClass('empty');
      var div = createElement('div', 'event empty');
      var span = createElement('span', '', 'No Events');

      div.appendChild(span);
      ele.appendChild(div);

    } else {
      events.forEach(function (ev) {
        var div = createElement('div', 'event');
        var p = createElement('p', 'timeP');
        // var span = createElement('span', '', ev.eventName);
        var spanW = createElement('span', '', '上班時間：' + ev.work);
        var spanB = createElement('span', '', '下班時間：' + ev.back);
        var square = createElement('div', 'event-category ' + ev.statusCategory);
        var calendar = createElement('span', '', ev.calendar);
        // div.appendChild(span);
        p.appendChild(spanW);
        p.appendChild(spanB);
        // 判斷有無',' 有的話印出所有分類跟顏色
        if (ev.statusCategory.includes(',') == true) {
          let statusCategorySplit = ev.statusCategory.split(',');
          let calendarSplit = ev.calendar.split(',');

          for (let i = 0; i < statusCategorySplit.length; i++) {
            switch (statusCategorySplit[i]) {
              case 'unCheckIn':
                var unCheckInSquare = createElement('div', 'event-category ' + statusCategorySplit[i]);
                var unCheckInCalendar = createElement('span', '', calendarSplit[i]);
                div.appendChild(unCheckInSquare);
                div.appendChild(unCheckInCalendar);
                break;

              case 'ontime':
                var ontimeSquare = createElement('div', 'event-category ' + statusCategorySplit[i]);
                var ontimeCalendar = createElement('span', '', calendarSplit[i]);
                div.appendChild(ontimeSquare);
                div.appendChild(ontimeCalendar);
                break;

              case 'late':
                var lateSquare = createElement('div', 'event-category ' + statusCategorySplit[i]);
                var lateCalendar = createElement('span', '', calendarSplit[i]);
                div.appendChild(lateSquare);
                div.appendChild(lateCalendar);
                break;

              case 'excused':
                var excusedSquare = createElement('div', 'event-category ' + statusCategorySplit[i]);
                var excusedCalendar = createElement('span', '', calendarSplit[i]);
                div.appendChild(excusedSquare);
                div.appendChild(excusedCalendar);
                break;

              default:
                break;
            }
          }
        } else {
          var square = createElement('div', 'event-category ' + ev.statusCategory);
          var calendar = createElement('span', '', ev.calendar);
          div.appendChild(square);
          div.appendChild(calendar);
        }
        div.appendChild(p);
        wrapper.appendChild(div);
      });

      if (!events.length) {
        var div = createElement('div', 'event empty');
        var span = createElement('span', '', 'No Events');

        div.appendChild(span);
        wrapper.appendChild(div);
      }

      if (currentWrapper) {
        currentWrapper.className = 'events out';
        currentWrapper.addEventListener('webkitAnimationEnd', function () {
          currentWrapper.parentNode.removeChild(currentWrapper);
          ele.appendChild(wrapper);
        });
        currentWrapper.addEventListener('oanimationend', function () {
          currentWrapper.parentNode.removeChild(currentWrapper);
          ele.appendChild(wrapper);
        });
        currentWrapper.addEventListener('msAnimationEnd', function () {
          currentWrapper.parentNode.removeChild(currentWrapper);
          ele.appendChild(wrapper);
        });
        currentWrapper.addEventListener('animationend', function () {
          currentWrapper.parentNode.removeChild(currentWrapper);
          ele.appendChild(wrapper);
        });
      } else {
        ele.appendChild(wrapper);
      }
    }
  }

  Calendar.prototype.drawLegend = function () {
    if (document.getElementsByClassName('legend').length > 0) {
      removeElementsByClass('legend');
    }
    var unCheckIn = createElement('span', 'unCheckIn entry', '未打卡');
    var ontime = createElement('span', 'ontime entry', '準時');
    var late = createElement('span', 'late entry', '遲到');
    var excused = createElement('span', 'excused entry', '早退');
    var legend = createElement('div', 'legend');
    legend.appendChild(ontime);
    legend.appendChild(late);
    legend.appendChild(excused);
    legend.appendChild(unCheckIn);
    this.el.appendChild(legend);
  }

  Calendar.prototype.nextMonth = function () {
    this.current.add('months', 1);
    this.next = true;
    this.draw();
  }

  Calendar.prototype.prevMonth = function () {
    this.current.subtract('months', 1);
    this.next = false;
    this.draw();
  }

  window.Calendar = Calendar;

  function createElement(tagName, className, innerText) {
    var ele = document.createElement(tagName);
    if (className) {
      ele.className = className;
    }
    if (innerText) {
      ele.innderText = ele.textContent = innerText;
    }
    return ele;
  }
}();

!function () {
  var data = []
  // 取得打卡資訊 渲染前端
  $.ajax({
    type: 'GET',
    url: 'api/check.php',
    data: { type: "showstatus" },
    contentType: "application/json; charset=utf-8",
    success: (result) => {
      var calendar = new Calendar('#calendar', result);
      calendar.drawMonth();
    }
  })
}();

function removeElementsByClass(className) {
  const elements = document.getElementsByClassName(className);
  while (elements.length > 0) {
    elements[0].parentNode.removeChild(elements[0]);
  }
}

window.addEventListener('resize', function windowResize() {
  let arrow = document.querySelector('.details .arrow');
  let activeday = document.querySelector('.week .activeday');
  let today = document.querySelector('.week .today');
  if (arrow) {
    if (activeday) {
      let detailsW = parseInt((activeday.parentNode.offsetWidth / 7) / 2);
      arrow.style.left = activeday.offsetLeft + detailsW + 'px';
    } else {
      let detailsW = parseInt((today.parentNode.offsetWidth / 7) / 2);
      arrow.style.left = today.offsetLeft + detailsW + 'px';
    }
  }
});