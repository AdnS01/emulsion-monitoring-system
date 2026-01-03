#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

const char* ssid     = "IP Test";
const char* password = "12345678900";

const char* serverName = "http://192.168.1.244/GEApplication/dataESP.php";

IPAddress local_ip(192, 168, 1, 243);
IPAddress gateway(192, 168, 1, 1);
IPAddress subnet(255, 255, 0, 0);

void setup() 
{
  Serial.begin(115200);
  
  WiFi.config(local_ip, gateway, subnet);

  Serial.println("");Serial.println("");Serial.print("Connecting");

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) { 
  delay(500); 
  Serial.print(".");}

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

void loop(){
  
  if (WiFi.status() == WL_CONNECTED)
  {
    String data = "";
    
    HTTPClient http;
    WiFiClient client;

    http.begin(client, serverName);              
    http.addHeader("Content-Type", "application/x-www-form-urlencoded"); 
    
    Serial.println();
    Serial.println();
    Serial.print("Waiting data");

    while (Serial.available() <= 0) {  
    Serial.print(".");
    delay(500);}
    
    data = Serial.readStringUntil('\n');

    Serial.println();
    Serial.print(data);
  
    int httpCode = http.POST(data);
    while(httpCode != 200){
      Serial.println();
      httpCode = http.POST(data);
      Serial.print("Erreur : "); 
      Serial.print(httpCode);     
    }

    String payload = http.getString();
    Serial.println();
    Serial.print(payload);
    
    http.end();

  } else{ Serial.println("WiFi Disconnected");}
  delay(5000);
}