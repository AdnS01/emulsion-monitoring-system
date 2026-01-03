# ESP8266 Gateway – UART to HTTP Bridge

## Purpose

This module runs on an **ESP8266** and acts as a **communication gateway** between the embedded controller (Arduino) and the backend server. Its role is to:
- Receive messages over **UART**
- Establish a **Wi-Fi connection**
- Forward data to a local server via **HTTP POST**
- Handle connectivity issues with retry mechanisms

The ESP8266 is deliberately used as a **transparent bridge**, keeping the Arduino firmware independent from network and protocol complexity.

## Network Configuration

- Static IP configuration (hard-coded, configurable at compile time)
- Local Wi-Fi network
- HTTP endpoint hosted on a local server

## Gateway Role in the System

          ┌─────────────┐
          │   Arduino   │
          └─────────────┘
                │ UART 
                ▼                                
     ┌───────────────────────┐               
     │ ESP8266 (ESP-01)      │               
     │ Serial → HTTP Gateway │               
     └──────────┬────────────┘               
                │ HTTP POST                    
                ▼                               
     ┌────────────────────────┐               
     │ PHP Backend + MySQL    │               
     │ Data storage & logging │               
     └──────────┬─────────────┘             
                │                               
                ▼                               
       ┌──────────────────┐               
       │ Web Application  │               
       │ Charts & History │
       └──────────────────┘      

The ESP8266 does **no control logic** and **no data interpretation**. It only ensures reliable transport of messages.
