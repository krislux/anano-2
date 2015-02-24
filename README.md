# Anano 2 PHP Framework

Anano is a nano-framework built in the style of larger frameworks such as Laravel, but intended to be as light and fast as possible, while still providing the most common developing aid you would expect from a full size framework.

Anano includes a DBAL/ORM (as well as built-in support for ActiveRecord, the lightest full ORM), migrations, simple IOC, full dynamic templating system, extensible CLI interface, as well as everything set up for Codeception testing.

Preliminary benchmarks clock Anano at around 10 times faster and with 1/20 the RAM consumption of a barebones Laravel 4 install.

**Note:** Anano was built because I needed to run many concurrent views on a less-than-optimal server, and Symfony and Laravel were simply too heavy. It's not nearly as well supported or thorougly tested as better known frameworks. Do not use this for larger projects requiring long term support, unless you know exactly what you're doing.

---

[MIT license](http://opensource.org/licenses/MIT).