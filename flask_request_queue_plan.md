# Investigation and Plan: Adding a Request Queue to Flask

## Investigation Results

### Current Capability
Flask (core) currently **does not have a built-in request queue**. In a typical deployment, Flask acts as a WSGI application. Requests are received from a WSGI server (like Gunicorn, uWSGI, or Waitress). These servers manage their own request queues/backlogs at the socket level.

### Search findings in `pallets/flask`
- A search for "queue" in the codebase returns no results related to request management.
- Issue #5427 ("Keep code running after response has been sent") was closed as "Not planned," indicating the maintainers' preference for keeping Flask's scope strictly to the request-response cycle.
- Flask's design philosophy (the "Micro" in microframework) dictates that features which can be handled by extensions or the environment (WSGI server, reverse proxy) should stay out of the core.

### Why a PR might be rejected
1. **Layer Violation:** Request queuing is traditionally the responsibility of the WSGI server or a reverse proxy (like Nginx).
2. **WSGI Constraints:** Flask follows the synchronous WSGI spec (mostly). Queueing internally would require complex thread/async management that conflicts with the "one request per worker" model.
3. **Complexity:** Adding a queue introduces concerns about timeouts, memory usage for queued requests, and prioritization, which are better handled by dedicated tools.

---

## Proposed Plan for Execution

If you still wish to proceed with proposing this feature or implementing it, here is a structured plan:

### Step 1: Formal Proposal (Issue)
Before writing any code, open a "Feature Request" issue on the `pallets/flask` repository.
- **Goal:** Gauge maintainer interest and get feedback on the design.
- **Content:** Describe the use case (e.g., preventing worker saturation, prioritizing certain endpoints) and why existing solutions (Gunicorn backlog, Celery for background tasks) are insufficient.

### Step 2: Implementation Strategy (WSGI Middleware)
Instead of modifying the `Flask` class directly, implement the queue as a WSGI middleware. This follows the decorator/wrapper pattern common in Flask.

```python
class RequestQueueMiddleware:
    def __init__(self, app, max_queue_size=10):
        self.app = app
        self.queue = queue.Queue(maxsize=max_queue_size)

    def __call__(self, environ, start_response):
        # Implementation of queueing logic here
        return self.app(environ, start_response)
```

### Step 3: Proof of Concept Extension
Create a new repository for `Flask-Request-Queue`.
- **Integrate with Flask signals:** Use `request_started` or `before_request` to manage internal state.
- **Testing:** Verify how it behaves under load with different WSGI servers.

### Step 4: Submission to Pallets (If encouraged)
If the maintainers respond positively to your issue:
1. Fork `pallets/flask`.
2. Implement the queueing logic, likely by wrapping `Flask.wsgi_app`.
3. Add comprehensive tests in `tests/test_app.py`.
4. Submit the PR with a reference to the discussed issue.

## Recommendation
For most use cases, it is recommended to:
- Use **Gunicorn's `--backlog`** or **Waitress's `connection_limit`** for simple queuing.
- Use **Celery** or **RQ** for long-running tasks.
- Use a **Reverse Proxy (Nginx/HAProxy)** for advanced request queue management and rate limiting.
