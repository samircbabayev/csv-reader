# CSV-READER

This is a Dockerized PHP project that allows you to download CSV with an open interface.

## Getting Started

### Prerequisites

- Docker
- Docker Compose

### Running the Project

1. Clone the repository:

   ```bash
   git clone https://gitlab.com/a6715752/test2.git

2. Navigate to the project directory

3. Create the downloads folder:
``` mkdir downloads ```
4. Set permissions
```chmod 777 downloads ```

5.Build and run the Docker containers:
``` docker-compose up ```


After downloading csv to interface you can filter products through

``` ?route=products&page=1&pageSize=100&category=health&gender=male&dob=1990-01-01&age=30&ageRangeStart=25&ageRangeEnd=30&```


```download_csv=1``` parameter allows download it to the downloads folder
