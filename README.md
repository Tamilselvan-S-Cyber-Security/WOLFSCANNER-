## Cyber Wolf – Smart Sensor Analytics
<p align="center">
  <img width="350" height="350" src="logo/logos.png">
</p>

**Hackathon**: The Hackindia Hackathon 2026 (MEC)  
**Team**: Cyber Wolf

### Project Purpose
- **Goal**: Turn raw user activity on web applications into structured security and behavior telemetry.  
- **Problem**: Most small teams lack an easy way to capture, standardize, and test security‑relevant events (page views, field edits, searches, localhost access) in a single pipeline.  
- **Solution**: Cyber Wolf provides a unified sensor layer that collects key interaction events, enriches them with context (IP, user, device, time), sends them to a central API, and logs everything for analysis, anomaly detection, and audit.

### Project Abstract
Cyber Wolf is a lightweight sensor framework for web applications that focuses on high‑quality, security‑aware event data. The system generates structured events for common user actions (page views, profile edits, searches, localhost/IPv6 access) and delivers them to a central sensor API with consistent formatting and logging. By cleaning the payload, converting complex structures to JSON, and persisting detailed logs, Cyber Wolf makes it easy to replay, inspect, and extend telemetry without changing core business logic. The result is a practical foundation for intrusion detection, usage analytics, and compliance reporting tailored for hackathon‑scale projects.

### Working Flowchart (High‑Level Logic)

```mermaid
flowchart TD
    U[User Activity]
    U --> W1[Page View]
    U --> W2[Field Edit]
    U --> W3[Search Event]
    U --> W4[Localhost or IPv6 Access]

    subgraph S1[Sensor Collection Layer]
        C1[Capture identity context]
        C2[Capture technical context]
        C3[Attach event payload]
        C4[Add UTC timestamp]
    end

    W1 --> C1
    W2 --> C1
    W3 --> C1
    W4 --> C1
    C1 --> C2 --> C3 --> C4

    subgraph S2[Pre Processing and Validation]
        P1[Remove empty and null fields]
        P2[Serialize list and object data to JSON]
        P3[Normalize event schema]
        P4[Prepare secure API payload]
    end

    C4 --> P1 --> P2 --> P3 --> P4

    subgraph S3[Sensor API Core]
        A1[Receive authenticated request]
        A2[Validate and classify event type]
        A3[Store event for analysis]
        A4[Return status and response message]
    end

    P4 --> A1 --> A2 --> A3 --> A4

    subgraph S4[Observation and Intelligence]
        O1[Log request and response details]
        O2[Build behavior timeline]
        O3[Detect anomalies and suspicious patterns]
        O4[Feed dashboard alerts and reports]
    end

    A3 --> O1 --> O2 --> O3 --> O4

    style S1 fill:#131220,stroke:#25eab5,stroke-width:1px,color:#d7e6e1
    style S2 fill:#1b1a2f,stroke:#5bcfbb,stroke-width:1px,color:#d7e6e1
    style S3 fill:#25213d,stroke:#f5b944,stroke-width:1px,color:#d7e6e1
    style S4 fill:#2c2c2c,stroke:#90a1b9,stroke-width:1px,color:#d7e6e1
```

```text
[User interacts with web app]
                |
                v
[Sensor layer builds structured event]
  - user identity (name, email, profile)
  - technical context (IP, URL, user-agent)
  - event type (page_view, field_edit, page_search, localhost_ipv6)
  - optional payload / field history
                |
                v
[Event pre-processing]
  - remove empty or None values
  - convert complex data (lists/dicts) to JSON strings
                |
                v
[Cyber Wolf Sensor API endpoint]
  - receive HTTP request
  - validate and store event
  - respond with status + message
                |
                v
[Logging & Observation Layer]
  - write detailed logs for each request/response
  - enable debugging and replay of test events
                |
                v
[Security & Analytics Use-Cases]
  - detect suspicious activity patterns
  - analyze usage (searches, page views, edits)
  - feed dashboards, alerts, and reports
```

### Key Features and Enhancements

| **Feature**                          | **Description**                                                                 | **Benefit for Hackindia 2026**                                  |
|--------------------------------------|---------------------------------------------------------------------------------|------------------------------------------------------------------|
| Unified sensor event model           | Standard schema for different user actions (views, edits, searches, localhost) | Easier to extend demo with new event types during the hackathon |
| Rich contextual metadata             | Captures IP, URL, user-agent, user identity, page titles, timestamps           | Better security analysis and user behavior insights              |
| Clean and structured payloads        | Removes empty fields and serializes complex structures to JSON                 | Stable, API‑friendly telemetry that is simple to store and query|
| Detailed request/response logging    | Logs every event sent and every API response                                    | Fast debugging on stage; transparent demo for judges            |
| IPv6 and special‑case support        | Explicit testing of localhost/IPv6 scenarios                                   | Shows attention to real‑world edge cases and infrastructure     |
| Plug‑and‑play test harness           | Reusable script to generate realistic test events                               | Quick reproducible demos, easy to showcase multiple scenarios   |


