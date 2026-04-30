# Android Location Permission Implementation Guide

## 1. AndroidManifest.xml Permissions
```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION" />
```

## 2. Runtime Permission Request (Java/Kotlin)
```java
// Check and request location permission
private void requestLocationPermission() {
    if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) 
        != PackageManager.PERMISSION_GRANTED) {
        
        ActivityCompat.requestPermissions(this, 
            new String[]{Manifest.permission.ACCESS_FINE_LOCATION}, 
            LOCATION_PERMISSION_REQUEST_CODE);
    } else {
        // Permission already granted, get location
        getCurrentLocation();
    }
}

// Handle permission result
@Override
public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
    super.onRequestPermissionsResult(requestCode, permissions, grantResults);
    
    if (requestCode == LOCATION_PERMISSION_REQUEST_CODE) {
        if (grantResults.length > 0 && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
            // Permission granted, get location
            getCurrentLocation();
        } else {
            // Permission denied, show rationale
            showLocationPermissionRationale();
        }
    }
}
```

## 3. FusedLocationProviderClient Setup
```java
private FusedLocationProviderClient fusedLocationClient;

@Override
protected void onCreate(Bundle savedInstanceState) {
    super.onCreate(savedInstanceState);
    
    fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);
}

private void getCurrentLocation() {
    if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) 
        != PackageManager.PERMISSION_GRANTED) {
        return;
    }
    
    fusedLocationClient.getCurrentLocation(Priority.PRIORITY_HIGH_ACCURACY, null)
        .addOnSuccessListener(this, location -> {
            if (location != null) {
                double latitude = location.getLatitude();
                double longitude = location.getLongitude();
                
                // Send to API with location
                submitAttendanceWithLocation(latitude, longitude);
            }
        })
        .addOnFailureListener(this, e -> {
            // Handle location failure
            showLocationError();
        });
}
```

## 4. Location Settings Check
```java
private void checkLocationSettings() {
    LocationRequest locationRequest = LocationRequest.create();
    locationRequest.setPriority(LocationRequest.PRIORITY_HIGH_ACCURACY);
    
    LocationSettingsRequest.Builder builder = new LocationSettingsRequest.Builder()
        .addLocationRequest(locationRequest);
    
    SettingsClient settingsClient = LocationServices.getSettingsClient(this);
    Task<LocationSettingsResponse> task = settingsClient.checkLocationSettings(builder.build());
    
    task.addOnSuccessListener(response -> {
        // Location settings are satisfied, get location
        requestLocationPermission();
    });
    
    task.addOnFailureListener(e -> {
        if (e instanceof ResolvableApiException) {
            // Location settings are not satisfied, show dialog
            ResolvableApiException resolvable = (ResolvableApiException) e;
            try {
                resolvable.startResolutionForResult(this, REQUEST_CHECK_SETTINGS);
            } catch (IntentSender.SendIntentException sendEx) {
                // Ignore the error
            }
        }
    });
}
```

## 5. User-Friendly Permission Rationale
```java
private void showLocationPermissionRationale() {
    new AlertDialog.Builder(this)
        .setTitle("Location Permission Required")
        .setMessage("This app needs location access to mark attendance with location verification. Please grant permission in settings.")
        .setPositiveButton("Grant Permission", (dialog, which) -> {
            // Open app settings
            Intent intent = new Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS);
            Uri uri = Uri.fromParts("package", getPackageName(), null);
            intent.setData(uri);
            startActivity(intent);
        })
        .setNegativeButton("Cancel", (dialog, which) -> {
            // Handle permission denial
            submitAttendanceWithoutLocation();
        })
        .show();
}
```

## Key Points:
1. **Standard Android Permission Flow**: Uses native Android permission dialogs
2. **Runtime Permission Check**: Required for Android 6.0+
3. **Location Settings Verification**: Ensures GPS/location services are enabled
4. **User-Friendly Messages**: Clear explanations for why location is needed
5. **Graceful Fallback**: Allows attendance submission even if location fails

This implementation will show the standard Android permission dialogs that users expect from mobile apps.
