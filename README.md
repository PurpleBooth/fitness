# Fitness

This is a toy project that allowed me to experiment with OAuth login and retrieving data from Googles Fitness API

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing 
purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

You'll need to install:
 * Docker
 * PHP 7.1

### Running

To start the project set these environment variables

* `GOOGLE_CLIENT_ID` - OAuth client ID from Google's API Credentials page
* `GOOGLE_CLIENT_SECRET` - OAuth secret ID from Google's API Credentials page

Then run

```bash
docker-compose up
```

This will run the server on [localhost:8000](http://localhost:8000)

## Contributing

We welcome contributions from anyone. 

* If it's a large change you'll probably want to talk about it in a ticket first.
* Please follow our [code of conduct](CODEOFCONDUCT.md) 

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/purplebooth/fitness/tags). 

## Authors

See the list of [contributors](https://github.com/purplebooth/fitness/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
