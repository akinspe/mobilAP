if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}

/* extend the date object for formatting */
Date.daysLong =    ["Sunday", "Monday", "Tuesday", "Wednesday", 
                       "Thursday", "Friday", "Saturday"];
Date.daysShort =   ["Sun", "Mon", "Tue", "Wed", 
                       "Thu", "Fri", "Sat"];
Date.monthsShort = ["Jan", "Feb", "Mar", "Apr",
                       "May", "Jun", "Jul", "Aug", "Sep",
                       "Oct", "Nov", "Dec"];
Date.monthsLong =  ["January", "February", "March", "April",
                       "May", "June", "July", "August", "September",
                       "October", "November", "December"];

Date.meridians = ["am","pm"];
Date.prototype.date = function() {
    d = new Date(this.getTime());
    d.setHours(0);
    d.setMinutes(0);
    d.setSeconds(0);
    d.setMilliseconds(0);
    return d;
}

/* Returns a string suitable for parsing by PHP's strtotime or date_parse */
Date.prototype.timetostr = function() {
    return Date.daysShort[this.getDay()] + ' ' + Date.monthsShort[this.getMonth()] + ' ' + this.getDate() + ' ' + this.getFullYear() + ' ' + this.getHours().leadingZero(2) + ':' + this.getMinutes().leadingZero(2) + ':' + this.getSeconds().leadingZero(2);
}

/* Returns hours based on 12 hour clock */
Date.prototype.getCivilianHours = function() {
    return this.getHours()>12 ? this.getHours()-12 : (this.getHours() == 0 ? 12 : this.getHours());
}

/* Returns number of days in the object's month */
Date.prototype.daysInMonth = function() {
    var days = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if (1==this.getMonth()) {
        var Y = this.getFullYear();
        if (         
            (Y % 4 == 0 && Y % 100 != 0) ||
            (Y % 4 == 0 && Y % 100 == 0 && Y % 400 == 0)
            ) {
            return 29;
        }
    }
    return days[this.getMonth()];
}

/* Returns a string with the number with a number of digits, padded with leading zeros */
Number.prototype.leadingZero = function(digits) {
    var str = this.toString();
    while (str.length < digits) {
        str = "0" + str;
    }
    return str;
}

/* Strip trailing spaces */
String.prototype.strip = function() {
    return this.replace(/^\s+/, '').replace(/\s+$/, '');
}
