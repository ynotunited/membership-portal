// States/Districts by Country
const statesByCountry = {
    'Nigeria': ['Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno', 
               'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT', 'Gombe', 'Imo', 
               'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 
               'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'],
    'Ghana': ['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 
             'Upper West', 'Volta', 'Brong-Ahafo', 'Savannah', 'Bono East', 'Ahafo', 'Oti', 'North East', 'Western North'],
    'United Kingdom': ['England', 'Scotland', 'Wales', 'Northern Ireland'],
    'United States': ['California', 'Texas', 'Florida', 'New York', 'Pennsylvania', 'Illinois', 'Ohio', 'Georgia', 'Other']
};

// Update State/District dropdown based on selected country
function updateStateOptions() {
    const countrySelect = document.getElementById('country');
    const stateSelect = document.getElementById('state_district');
    const selectedCountry = countrySelect.value;
    
    // Clear existing options
    stateSelect.innerHTML = '<option value="">Select your state or district</option>';
    
    if (selectedCountry && statesByCountry[selectedCountry]) {
        const states = statesByCountry[selectedCountry];
        states.forEach(state => {
            const option = document.createElement('option');
            option.value = state;
            option.textContent = state;
            stateSelect.appendChild(option);
        });
        stateSelect.disabled = false;
    } else {
        stateSelect.innerHTML = '<option value="">First select your country</option>';
        stateSelect.disabled = true;
    }
    
    // Clear chapter when country changes
    document.getElementById('chapter').value = '';
}

// Update Chapter field based on selected State/District
function updateChapter() {
    const stateSelect = document.getElementById('state_district');
    const chapterInput = document.getElementById('chapter');
    const selectedState = stateSelect.value;
    
    if (selectedState) {
        // Chapter is the same as the state/district
        chapterInput.value = selectedState + ' Chapter';
    } else {
        chapterInput.value = '';
    }
}
