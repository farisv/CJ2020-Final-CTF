#include <iostream>
#include <map>
#include <vector>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
using namespace std;

#define MAXN 1000

size_t p = 1;
char *airport[MAXN];
map<string,int> index_map;
bool lockdown[MAXN];
unsigned long long connection[MAXN][MAXN];

void setup() { setvbuf(stdout, 0, 2, 0); }

void lock_airport() {
   char name[512];

    cout << "Airport name: " << endl;
    cin >> name;

    if (index_map[name] == 0) {
        cout << "Unknown airport" << endl;
        return;
    }

    if (lockdown[index_map[name]]) {
        cout << "Airport " << name << " is under lockdown" << endl;
        return;
    }

    lockdown[index_map[name]] = true;
    free(airport[index_map[name]]);
    cout << "Airport " << name << " is now under lockdown" << endl;
}

void airport_list() {
    size_t i;
    cout << "Available airport:" << endl;
    for (i = 1; i < p; i++) {
        if (!lockdown[i]) {
            cout << airport[i] << endl;
        }
    }
}

void connection_list() {
   char name[512];

    cout << "Airport name: " << endl;
    cin >> name;

    if (index_map[name] == 0) {
        cout << "Unknown airport" << endl;
        return;
    } else {
        cout << "Connections: " << endl;
        for (int i = 1; i <= MAXN; i++) {
            if (connection[index_map[name]][i] > 0) {
                cout << airport[i] << " " << connection[index_map[name]][i] << endl;
            }
        }
    }
}

void change_name() {
    char name1[512], name2[512];

    cout << "Airport name: " << endl;
    cin >> name1;

    cout << "Airport new name: " << endl;
    cin >> name2;

    bool ok = false;

    for (int i = 1; i < p; i++) {
        if (strcmp(airport[i], name1) == 0) {
            strncpy(airport[i], name2, strlen(name2));
            ok = true;
            break;
        }
    }

    if (ok) {
        cout << "Changed" << endl;
    } else {
        cout << "Something's wrong" << endl;
    }
}

void add_connection() {
    char name1[512], name2[512];

    cout << "Airport 1 name: " << endl;
    cin >> name1;

    if (index_map[name1] == 0) {
        cout << "Unknown airport" << endl;
        return;
    }

    if (lockdown[index_map[name1]]) {
        cout << "Airport " << name1 << " is under lockdown" << endl;
        return;
    }

    cout << "Airport 2 name: " << endl;
    cin >> name2;

    if (index_map[name2] == 0) {
        cout << "Unknown airport" << endl;
        return;
    }

    if (lockdown[index_map[name2]]) {
        cout << "Airport " << name2 << " is under lockdown" << endl;
        return;
    }

    if (index_map[name1] == index_map[name2]) {
        cout << "Can't connect the same airport" << endl;
        return;
    }

    cout << "Distance: ";

    unsigned long long dist;

    cin >> dist;

    if (dist < 1) {
        cout << "Invalid distance" << endl;
        return;
    }

    connection[index_map[name1]][index_map[name2]] = dist;
    
    cout << "Connected" << endl;
}

void add_airport() {
    char buffer[512];
    int index;

    cout << "Airport name: " << endl;
    cin >> buffer;

    if (index_map[buffer] > 0 || index_map[buffer] == -1) {
        cout << "Duplicate airport name" << endl;
        return;
    } else {
        index_map[buffer] = p;
    }

    airport[p] = (char *)malloc(strlen(buffer));
    strncpy(airport[p], buffer, strlen(buffer));
    p++;
}

void menu() {
    char c;

    cout << "<<<<<<<<<< CJ Airport System >>>>>>>>>>" << endl;

    while (1) {
        cout << "1) Add airport" << endl;
        cout << "2) Add connection" << endl;
        cout << "3) Change airport name" << endl;
        cout << "4) Lockdown" << endl;
        cout << "5) List of available airport" << endl;
        cout << "6) List of connections" << endl;
        cout << "7) Exit" << endl;
        cout << "Choice: ";
        cin >> c;
        if (c == '1') {
            add_airport();
        } else if (c == '2') {
            add_connection();
        } else if (c == '3') {
            change_name();
        } else if (c == '4') {
            lock_airport();
        } else if (c == '5') {
            airport_list();
        } else if (c == '6') {
            connection_list();
        } else {
            break;
        }
    }
}

int main() {
    setup();
    menu();
    return 0;
}
