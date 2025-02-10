
# Pomodoro Timer Api

This is a RESTful API for tracking Pomodoro timer sessions, built using Symfony and containerized with Docker.


## Run Locally

Clone the project

```bash
  git clone https://link-to-project
```

Go to the project directory

```bash
  cd my-project
```

Install dependencies for the first time and start

```bash
  make setup
```

Start the server (If you just ran setup this is not needed)

```bash
  make up
```

Restart the server

```bash
  make restart
```
## API Reference

#### Start a new timer

```http
  POST /api/timer/start
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `user_id` | `string` | **Required**. Your User ID |
| `task_description` | `string` | **Optional**. Your timer description |

#### Stop a timer

```http
  PUT /api/timer/stop/{userId}/{sessionId}
```

#### Get sessions by user

```http
  GET /api/timer/sessions/{userId}
```

#### Create a new user

```http
  POST /api/user/create
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `username` | `string` | **Required**. Your username |

#### Get a user

```http
  GET /api/user/{id}
```

#### Delete a user

```http
  DELETE /api/user/{id}
```

#### Update user settings

```http
  PUT /api/user/update-settings/{id}
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `work_duration` | `string` | **Optional**. Length of working time |
| `short_break_duration` | `string` | **Optional**. Length of short break |
| `long_break_duration` | `string` | **Optional**. Length of long break |
| `break_interval` | `string` | **Optional**. Interval before longer break |


