if (!window.dashcode) {
    dashcode = new Object();
}

//
// cloneTemplateElement(element, isTemplate)
// Clone an element and initialize the parts it contains. The new element is simply returned and not added to the DOM.
//
// element: element to clone
// isTemplate: true if this is the template element
//
dashcode.cloneTemplateElement = function (element, isTemplate) {
    // clone the node and its subtree
    var newElement = isTemplate ? element : element.cloneNode(true);
    var templateElements = new Object();
    this.processClonedTemplateElement(newElement, templateElements, isTemplate);
    
    if( !newElement.object ) newElement.object = {};
    
    newElement.object.templateElements = templateElements;
    
    // finish loading parts that need post-processing
    for (var key in templateElements) {
        if (templateElements[key].object && templateElements[key].object.finishLoading) {
            templateElements[key].object.finishLoading();
        }
    }
    
    return newElement;
}

//
// processClonedTemplateElement(element, templateElements, isTemplate, preserveIds)
// Recursively process a newly cloned template element to remove IDs and initialize parts.
//
// element: element to process
// templateElements: list of references to template objects to populate
// isTemplate: true if this is the template element
// preserveIds: true to preserve the original id in a tempId attribute
//
dashcode.processClonedTemplateElement = function (element, templateElements, isTemplate, preserveIds) {
    var originalID = element.id;
    if (!originalID && element.getAttribute) {
        if (originalID = element.getAttribute("tempId")) {
            element.removeAttribute("tempId");
        }
    }
    var partSpec = null;
    if (originalID) {
        //partSpec = dashcodePartSpecs[originalID];
    }
    // process the children first
    var preserveChildIds = preserveIds || (partSpec && partSpec.preserveChildIdsWhenCloning);
    var children = element.childNodes;
    for (var f=0; f<children.length; f++) {
        arguments.callee(children[f], templateElements, isTemplate, preserveChildIds);
    }
    if (originalID) {
        templateElements[originalID] = element;
        if (!isTemplate) { 
            element.removeAttribute("id");
            if (preserveIds) {
                element.setAttribute("tempId", originalID);
            }
            // if it's a 'part', initialize it
            if (partSpec) {
                partSpec.originalID = originalID;
                var createFunc = window[partSpec.creationFunction];
                if (createFunc && createFunc instanceof Function) {
                    createFunc(element, partSpec);
                }
            }
        }
    }
}
