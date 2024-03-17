```
# Winning Lottery

## Overview
Winning Lottery is a powerful tool for lottery enthusiasts, leveraging statistical analysis to increase the odds of selecting winning lottery numbers. It integrates sophisticated algorithms and historical lottery draw data to suggest the most probable combinations. Ideal for users looking to enhance their lottery strategy with data-driven insights.

## Features
- Generates lottery number combinations based on various strategies:
  - High-low distribution
  - Frequency of number occurrence (least and most picked numbers)
  - Purely random selection
- Offers the option to order generated numbers by preference (e.g., high to low, low to high).
- Compares generated tickets against the last 6 months of lottery draws to check for potential wins.
- Provides a summary of results, including total winnings versus the cost of tickets, to evaluate the strategy's effectiveness.

## Prerequisites
- PHP 8.1 or higher.
- Composer for dependency management.

## Installation
Clone the repository and install dependencies using Composer:

```bash
git clone https://github.com/hackzilla/WinningLottery.git
cd WinningLottery
composer install
```

## Configuration
The application is ready to use after installation. Ensure you have PHP 8.1 or higher installed on your system.

## Usage
### Generating Lottery Numbers
Use the Symfony console command to generate lottery numbers:

```bash
bin/console app:generate [options]
```

Options:
- `--balls` (`-b`): Specify the total number of balls (default: 59).
- `--order` (`-o`): Order of balls (none, high-low, low-high, least-picked, most-picked, random).
- `--result` (`-r`): Check tickets against the last 6 months of lottery draws.
- `--summary` (`-s`): Get a summary of the results.

Example:

```bash
bin/console app:generate -b 59 -o high-low -r -s
```

This command generates numbers, checks them against the last 6 months of draws [National Lottery, Lotto], and displays a summary of the outcomes.

## Testing

The project includes a few of unit tests. Run these tests to ensure everything is working correctly:

```bash
./vendor/bin/phpunit
```

## Contributions and Issues
See all contributors on [GitHub](https://github.com/hackzilla/WinningLottery/graphs/contributors).

Please report issues using GitHub's issue tracker: [GitHub Repo](https://github.com/hackzilla/WinningLottery)

If you find this project useful, consider [buying me a coffee](https://www.buymeacoffee.com/hackzilla).

## License
© 2024 by Daniel Platt is licensed under [CC BY-NC-SA 4.0](http://creativecommons.org/licenses/by-nc-sa/4.0/)
