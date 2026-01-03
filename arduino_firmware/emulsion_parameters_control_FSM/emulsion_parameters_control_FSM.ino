#include <Arduino.h>  
#include <U8g2lib.h>  // LCD

#ifdef U8X8_HAVE_HW_SPI // use hardware SPI
#include <SPI.h>
#endif

// LCD: ST7920 128x64 (software SPI)
// clock=en=13 , data=MOSI=R/W=11 , CS=ss=RS=10, reset=8;
U8G2_ST7920_128X64_F_SW_SPI u8g2(U8G2_R0, 13, 11, 10, 8); 


/* --------------------------- Pins --------------------------- */
const byte interruptPin = 2;    // Machine ON/OFF input (digital) 
const byte LED_V = 3;           // OK / No correction 
const byte LED_B = 4;           // Adding water indicator 
const byte LED_R = 5;           // Adding oil indicator
const byte Pompe = 6;           // Oil pump contactor
const byte ElecVanne = 7;       // Water electrovalve
const byte ConstrationPin = A0; // Analog sensor input (refractometer 4–20mA -> resistor)


/* ------------------------- Variables ------------------------- */
volatile bool SMach = true;            // machine ON/OFF
bool ErrColl = false;                  // sampling / reading error
bool ErrAdd  = false;                  // correction error
float SommMDC, ConAverage, dev, T = 0; // sum of MDC(main data collection), concentration average
int NbrMDC = 0;                        // number of samples

// Sensor scaling (4–20 mA -> 6–9%)
const float resistance = 275.0;  // Ohm
const float courant_min = 4.0;   // mA
const float courant_max = 20.0;  // mA
const float con_min = 6.00;      // %
const float con_max = 9.00;      // %
float coeff_a = (con_max - con_min) / (courant_max - courant_min);
float coeff_b = con_min - coeff_a * courant_min;


/* ---------------------- State machine ---------------------- */
enum State {
  S_Init,           // initialization and idle state
  S_DataColl,       // data collection state 
  S_AddDec,         // addition decision state
  S_AddWater,       // water addition state
  S_AddOil,         // oil addition state
  S_Verify          // verification state
};

volatile State StateMachine = S_Init; 

void setup() {
 	Serial.begin(115200);
  
  //AnalogRead
  analogReference(DEFAULT);
  pinMode(ConstrationPin, INPUT_PULLUP);

  //interrupt Machine ON/OFF
  pinMode(interruptPin, INPUT_PULLUP);
  //interruptPin(2) switches from LOW to HIGH
 	attachInterrupt(digitalPinToInterrupt(interruptPin), IsrOff, HIGH);
  //interruptPin(2) switches from HIGH to LOW
  attachInterrupt(digitalPinToInterrupt(interruptPin), IsrOn, LOW);

  //outputs
  pinMode(ElecVanne, OUTPUT);
  pinMode(Pompe, OUTPUT);
  pinMode(LED_B, OUTPUT);
  pinMode(LED_R, OUTPUT);
  pinMode(LED_V, OUTPUT);

  //LCD
  u8g2.begin(); //initialiser lcd
  u8g2.setFont(u8g2_font_ncenB08_tr);	// choose a suitable font
}

void loop() {

  switch(StateMachine){

    case S_Init :
      InitOutputs();
      ErrColl = false;
      ErrAdd = false;

      SendOp("Initialization");
      Draw1R(30, "Initialization");

      // Wait until machine ON and no errors
      while (!(SMach && !ErrColl && !ErrAdd)){
        delay(1000);
      }
      
      StateMachine = S_DataColl;
    break;

    case S_DataColl :
      if(!SMach || ErrColl || ErrAdd){
        StateMachine = S_Init;
      }else{
        InitOutputs();
        SendOp("Data collection");
        Draw1R(22,"Data collection");
        u8g2.clearBuffer(); 

        digitalWrite(LED_V, HIGH);

        int i = 1;
	      int row = 12; 
        ConAverage = NbrMDC = SommMDC = 0;

        do{
          DrawSimp(row, i); 
          delay(3000);

          float MDC = Concentration(analogRead(ConstrationPin));
	        DrawDone(row);
          
          if(MDC < 6 || MDC >= 9){
            SendOp("Data reading error : pin A0");
            Draw2R(10,"Data reading error :",40, "Pin A0");
            ErrColl = true;
            StateMachine = S_Init;
          }else{
            SendCn(MDC, "Main data collection");
            SommMDC += MDC;
            NbrMDC++;
		        i++;
		        row += 12;
          }
        }while(NbrMDC != 5);

        digitalWrite(LED_V, LOW);

        StateMachine = S_AddDec;
      }
    break;

    case S_AddDec :
      ConAverage = SommMDC/NbrMDC;
      SendCn(ConAverage, "Concentration average");

      if(ConAverage > 8){
        StateMachine = S_AddWater;
      }else if(ConAverage<7){
        StateMachine = S_AddOil;
      }else{
        SendOp("Operationg : No addition");
        Draw2R(25,"Concentration", 30,"is compliant");
        StateMachine = S_DataColl;
      }
    break;

    case S_AddWater :
      dev = T = 0;
      dev = ConAverage - 8.00;
      T = (dev * 60000.0) / 0.1;

      digitalWrite(ElecVanne, HIGH);
      digitalWrite(LED_B, HIGH);

      SendOp("Operating : Adding Water");
	    Draw3R(28, "Adding Water", ConAverage);

      delay(T); 

      SendOp("Operating : End of addition");
	    Draw2R(35,"Operating :",20,"End of addition");

      StateMachine = S_Verify;
    break;

    case S_AddOil :
      dev = T = 0;
      dev = 7.00 - ConAverage;
      T = (dev * 20000.0) / 0.1;

      digitalWrite(Pompe, HIGH);
      digitalWrite(LED_R, HIGH);

      SendOp("Operating : Adding Oil");
      Draw3R(35, "Adding Oil", ConAverage);

      delay(T);

      SendOp("Operating : End of addition");
	    Draw2R(35,"Operating :",20,"End of addition");

      StateMachine = S_Verify;
    break;

    case S_Verify :
      SendOp("Operating : Data Collection Verification");
	    Draw2R(35,"Operating :",20,"Verification  . . .");

      delay(3000);

      float DCV = Concentration(analogRead(analogRead(ConstrationPin))); //DCV: Data Collection Verification
      
      if(DCV < 6 || DCV >= 9){
        SendOp("Data reading error : pin A0");
        Draw2R(10,"Data reading error :",40, "Pin A0");
        ErrColl = true;
        StateMachine = S_Init;
      }else if(DCV <= ConAverage){  // If verification does not improve, flag error
        SendOp("Error : Operation is not executed properly");
        Draw2R(10, "Error : Operation is", 5, "not executed properly");
        ErrAdd = true;
        StateMachine = S_Init;
      }else { // If verification improved, go back to decision
        StateMachine = S_AddDec;
      }
    break;
  }
}


/* ------------------------- Helpers ------------------------- */
void IsrOff(){
 	StateMachine = S_Init;
  SMach = false;
}

void IsrOn(){
 	StateMachine = S_Init;
  SMach = true;
}

void InitOutputs(){
  digitalWrite(Pompe, LOW);
  digitalWrite(ElecVanne, LOW);
  digitalWrite(LED_B, LOW);
  digitalWrite(LED_R, LOW);
  digitalWrite(LED_V, LOW);
}

// ADC -> voltage -> current(mA) -> concentration(%)
float Concentration(int ValAnalogique){
  float tension, courant, concentration = 0;
  tension = ValAnalogique * (5.0 / 1024.0);
  courant = tension / resistance * 1000; // mA
  concentration = coeff_a * courant + coeff_b;
  return concentration;
}


/* ------------------------- Serial sending ------------------------- */

void SendOp(const char *ACTION){
  String mdp = "GEAmdpREQUEST";
  String tab2 = "A";
  String data = "mdp=" + mdp + "&tab=" + tab2 + "&ACTION=" + ACTION + "";
  Serial.println(data);
}

void SendCn(float C, const char *TYPE){
  String mdp = "GEAmdpREQUEST";
  String tab1 = "P";
  String data = "mdp=" + mdp + "&tab=" + tab1 + "&VALEUR=" + C + "&TYPE=" + TYPE + "";
  Serial.println(data);
}


/* ------------------------- LCD drawing ------------------------- */
void Draw1R(int emp, const char *str){
  u8g2.clearBuffer();
  u8g2.drawStr(0,10,"-----------------------------------------------");
  u8g2.drawStr(emp, 35, str);  
  u8g2.drawStr(0,62,"-----------------------------------------------");
  u8g2.sendBuffer();
  delay(5000);
}

void Draw2R(int emp1, const char *str1, int emp2, const char *str2){
  u8g2.clearBuffer();
  u8g2.drawStr(0,5,"-----------------------------------------------");
  u8g2.drawStr(emp1, 25, str1);  
  u8g2.drawStr(emp2, 45, str2);
  u8g2.drawStr(0,67,"-----------------------------------------------");
  u8g2.sendBuffer();
  delay(5000);
}

void Draw3R(int emp, const char *str, float ConAverage){ 
  u8g2.clearBuffer();
  u8g2.drawStr(0, 15, "-------- Operating : --------");
  u8g2.drawStr(emp, 35, str);
  u8g2.drawStr(38, 55, "Ca = ");  
  u8g2.setCursor(63, 55);
  u8g2.print(ConAverage);
  u8g2.drawStr(85, 55, "%");
  u8g2.sendBuffer();
  delay(5000);
}

void DrawSimp(int row, int NbrMDC){
  u8g2.drawStr(5, row, "Sampling");
  u8g2.setCursor(65, row);
  u8g2.print(NbrMDC);
  u8g2.drawStr(75, row, ":");
  u8g2.sendBuffer();
}
void DrawDone(int row){
  u8g2.drawStr(85, row, "is done");
  u8g2.sendBuffer();
}
