/*
	Copyright 2020 - Roberto Guido <bob@linux.it>

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

(function ($) {
	function putEvents(container, date, cell)
	{
		date = [(date.getYear() + 1900), (date.getMonth() + 1), date.getDate()].join('-');
		container.ContinuousCalendar_options.events.forEach(function(e, index) {
			if (e.date == date) {
				let link = $('<a>');
				link.addClass(e.className || 'event');

				if (typeof e.url !== "undefined") {
					link.attr('href', e.url);
				}

				link.text(e.title);
				cell.append(link);
			}
		});
	}

	function renderCurrentGrid(container)
	{
		var b = container.find('tbody');
		b.empty();

		var today = new Date();
		var date = new Date(container.startDate);

		for(var i = 0; i < container.ContinuousCalendar_options.rows; i++) {
			var row = $('<tr>');
			b.append(row);

			var starter = $('<td>');
			starter.addClass('currentmonth');
			row.append(starter);

			for(var a = 0; a < 7; a++) {
				let day = $('<span>');
				day.text(date.getDate());

				let cell = $('<td>');
				cell.addClass('day-in-month-' + (date.getMonth() % 2));
				if (date.isSameDateAs(today)) {
					cell.addClass('today');
				}

				cell.append(day);
				row.append(cell);

				if (date.getDate() == 1) {
					starter.text(container.ContinuousCalendar_options.months[date.getMonth()] + ' ' + (date.getYear() + 1900));
				}

				putEvents(container, date, cell);

				date.setDate(date.getDate() + 1);
			}
		}
	}

	function goBack(subject)
	{
		subject.startDate.setDate(subject.startDate.getDate() - 7);
		renderCurrentGrid(subject);
	}

	function goForward(subject)
	{
		subject.startDate.setDate(subject.startDate.getDate() + 7);
		renderCurrentGrid(subject);
	}

	Date.prototype.isSameDateAs = function(d) {
		return (
			this.getFullYear() === d.getFullYear() &&
			this.getMonth() === d.getMonth() &&
			this.getDate() === d.getDate()
		);
	}

	$.fn.ContinuousCalendar = function(options) {
		var defaults = {
			days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
			months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
			rows: 5,
			tableClass: 'continuous-calendar table',
			events: [],
		};

		this.ContinuousCalendar_options = $.extend({}, defaults, options || {});

		/*
			This is to normalize events' dates without trailing zeroes for month and day
		*/
		this.ContinuousCalendar_options.events.forEach(function(e, index) {
			let tokens = e.date.split('-');
			e.date = [parseInt(tokens[0]), parseInt(tokens[1]), parseInt(tokens[2])].join('-');
		});

		var table = $('<table>');
		table.addClass(this.ContinuousCalendar_options.tableClass);
		this.append(table);

		var subject = this;

		var header = $('<tr>');

		var prev = $('<a href="#" class="prev">&lt;</a>');
		prev.click(function(e) {
			e.preventDefault();
			goBack(subject);
		});

		var next = $('<a href="#" class="next">&gt;</a>');
		next.click(function(e) {
			e.preventDefault();
			goForward(subject);
		});

		var nav_cell = $('<th>');
		nav_cell.append(prev);
		nav_cell.append(next);
		header.append(nav_cell);

		this.ContinuousCalendar_options.days.forEach(function(item, index) {
			header.append('<th>' + item + '</th>');
		});

		var h = $('<thead>');
		h.append(header);
		table.append(h);

		var b = $('<tbody>');
		table.append(b);

		this.startDate = new Date();
		this.startDate.setDate(this.startDate.getDate() - (this.startDate.getDay() + 6) % 7);

		renderCurrentGrid(subject);

		this.bind('wheel', function(e) {
			e.preventDefault();
			e.stopPropagation();

			if (e.originalEvent.deltaY / 120 > 0) {
				goBack(subject);
			}
			else {
				goForward(subject);
			}
		});

		return this;
	};
}(jQuery));
