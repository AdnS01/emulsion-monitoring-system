# Arduino Firmware – Emulsion Parameters Control

## Purpose

This firmware runs on an **Arduino** (ATmega-based) and implements the **control logic** of an industrial emulsion monitoring system used in a copper wire drawing process.

Main functions:
- Measure emulsion concentration from a 4–20 mA sensor
- Apply decision logic using a Finite State Machine (FSM)
- Control actuators (oil pump, water electrovalve)
- Provide local status feedback (LCD & LEDs)
- Send formatted telemetry messages over UART to an ESP8266 Wi-Fi gateway

## Control Logic

The firmware is implemented as a **deterministic FSM** with the following states:

| State        | Role                            |
|--------------|---------------------------------|
| `S_Init`     | Initialization and safe reset   |
| `S_DataColl` | Sampling and averaging          |
| `S_AddDec`   | Decision based on concentration |
| `S_AddWater` | Water addition                  |
| `S_AddOil`   | Oil addition                    |
| `S_Verify`   | Post-correction verification    |

## Hardware Interfaces

### Inputs
- Analog (A0): Refractometer output (4–20 mA via shunt resistor)
- Digital (interrupt): Machine ON/OFF signal

### Outputs
- Oil pump (relay, 24 V)
- Water electrovalve (relay, 24 V)
- Status LEDs
- LCD ST7920 (SPI)
- ESP8266 (UART)

## Hardware block diagram

```text

             ┌──────────────────────────┐                               
             │  Refractometer Sensor    │                               
             │  (4–20 mA output)        │                               
             └─────────────┬────────────┘                               
                           │ 4–20 mA                                     
                           ▼                                                                                        
      ┌─────────────────────────────────────────────────────┐   
      │                  Arduino                            │   
      │  ┌──────────────┐  ┌─────────────────────────────┐  │   
      │  │ ADC (A0)     │  │  Finite State Machine (FSM) │  │   
      │  │ Concentration│  └─────────────────────────────┘  │
      │  │ computation  │                                   │
      │  └──────────────┘                                   │   
      │  ┌──────────────┐        ┌──────┐    ┌─────┐        │  SPI    ┌────────┐ 
      │  │ Digital I/O  │        │ UART │    │ SPI │----------------> │  LCD   │
      │  │ (Relays,LEDs)│        │      │    │     │        │         └────────┘
      │  └──────┬───────┘        └───┬──┘    └─────┘        │   
      └─────────┼────────────────────┼──────────────────────┘   
                │ output             │ UART                           
                ▼                    ▼                                
    ┌────────────────────────┐  ┌───────────────────────┐               
    │ Relay Interface (24 V) │  │ ESP8266 (ESP-01)      │               
    └───────────┬────────────┘  │ Serial → HTTP Gateway │               
       ┌────────┴────────┐      └─────────┬─────────────┘               
       │                 │                │ HTTP POST                    
       ▼                 ▼                ▼                               
┌─────────────┐   ┌─────────────┐   ┌────────────────────────┐               
│ Electrovalve│   │ Oil Pump    │   │ PHP Backend + MySQL    │               
│ (Water add) │   │ (Oil add)   │   │ Data storage & logging │               
└─────────────┘   └─────────────┘   └──────────┬─────────────┘              
                                               │                               
                                               ▼                               
                                      ┌──────────────────┐      
                                      │ Web Application  │               
                                      │ Charts & History │               
                                      └──────────────────┘
```