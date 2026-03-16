<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:csd="urn:ihe:iti:csd:2013"
    exclude-result-prefixes="xs" version="1.0">

    <xsl:param name="rootProviderURN"/>
    <xsl:param name="rootFacilityURN"/>
    <xsl:param name="rootOrganizationURN"/>
    <xsl:param name="sourceDirectory"/>
    <xsl:param name="facility_typeCodingScheme"/>
    <xsl:param name="currentDateTime"/>

    <xsl:variable name="workContactPointScheme">urn:ihe:iti:csd:2013:contactPoint</xsl:variable>
    <xsl:variable name="workAddressType">Practice</xsl:variable>
    <xsl:variable name="workMobileType">BP</xsl:variable>
    <xsl:variable name="workPhoneType">BP</xsl:variable>
    <xsl:variable name="workFaxType">FAX</xsl:variable>
    <xsl:variable name="workEmailType">EMAIL</xsl:variable>


    <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"  omit-xml-declaration="yes"/>

    <xsl:template match="/">
      <xsl:for-each
          select="/relationship | /relationshipCollection/relationship">
        <xsl:variable name="facilityURN">urn:uuid:<xsl:value-of select="normalize-space(form[@name='facility']/field[@name='csd_uuid']/text())"/></xsl:variable>
        <xsl:variable name="created"><xsl:value-of select="translate(normalize-space(form[@name='region']/@created),' ','T')"/></xsl:variable>
        <xsl:variable name="modified"><xsl:value-of select="$currentDateTime"/></xsl:variable>
        <xsl:variable name="workContact" select="joinedForms/joinedForm[@report_form_name='facility_contact']/form[@name='facility_contact']"/>
        <xsl:variable name="name" select="normalize-space(form[@name='facility']/field[@name='name']/text())"/>
        <xsl:variable name="code" select="normalize-space(form[@name='facility']/field[@name='code']/text())"/>
        <xsl:variable name="latitude" select="normalize-space(form[@name='facility']/field[@name='latitude']/text())"/>
        <xsl:variable name="longitude" select="normalize-space(form[@name='facility']/field[@name='longitude']/text())"/>
        <xsl:variable name="hidden" select="normalize-space(form[@name='facility']/field[@name='i2ce_hidden']/text())"/>
        <xsl:variable name="type" select="normalize-space(form[@name='facility']/field[@name='facility_type']/value/text())"/>
        <xsl:variable name="county" select="joinedForms/joinedForm[@report_form_name='county']/form[@name='county']/field[@name='csd_uuid']/text()"/>
        <xsl:variable name="district" select="joinedForms/joinedForm[@report_form_name='district']/form[@name='district']/field[@name='csd_uuid']/text()"/>



        <xsl:if test="$name != '' and $type != ''">
	  <csd:facility>
            <xsl:attribute name="entityID"><xsl:value-of select="$facilityURN"/></xsl:attribute>
       <csd:otherID>
			<xsl:attribute name='assigningAuthorityName'><xsl:value-of select="$sourceDirectory"/></xsl:attribute>
			<xsl:attribute name='code'>urn:ihris.org:form:facility</xsl:attribute>
			<xsl:value-of select="normalize-space(string(form[@name='facility']/@id))"/>
		 </csd:otherID>
     <xsl:if test="$code != ''">
       <csd:otherID>
         <xsl:attribute name='assigningAuthorityName'><xsl:value-of select="$sourceDirectory"/></xsl:attribute>
         <xsl:attribute name='code'>code</xsl:attribute>
         <xsl:value-of select="$code"/>
       </csd:otherID>
     </xsl:if>
     <xsl:if test="$latitude != '' or $longitude != ''">
       <csd:geocode>
         <csd:latitude><xsl:value-of select="$latitude"/></csd:latitude>
         <csd:longitude><xsl:value-of select="$longitude"/></csd:longitude>
       </csd:geocode>
     </xsl:if>
	    <csd:codedType>
	      <xsl:attribute name='code'><xsl:value-of select="$type"/></xsl:attribute>
              <xsl:attribute name="codingScheme"><xsl:value-of select="$facility_typeCodingScheme"/></xsl:attribute>
	    </csd:codedType>
	    <csd:primaryName><xsl:value-of select="$name"/></csd:primaryName>


            <xsl:variable name="workAddress"><xsl:value-of select="normalize-space($workContact/field[@name='address']/text())"/></xsl:variable>
            <xsl:variable name='telephone'><xsl:value-of select="normalize-space($workContact/field[@name='telephone']/text())"/></xsl:variable>
            <xsl:variable name='alt_telephone'><xsl:value-of select="normalize-space($workContact/field[@name='alt_telephone']/text())"/></xsl:variable>
            <xsl:variable name='mobile'><xsl:value-of select="normalize-space($workContact//field[@name='mobile_phone']/text())"/></xsl:variable>
            <xsl:variable name='fax'><xsl:value-of select="normalize-space($workContact/field[@name='fax']/text())"/></xsl:variable>
	    <xsl:variable name='email'><xsl:value-of select="normalize-space($workContact/field[@name='email']/text())"/></xsl:variable>

            <xsl:if test="$mobile != ''">
              <csd:contactPoint><csd:codedType>
                <xsl:attribute name='codingScheme'><xsl:value-of select="$workContactPointScheme"/></xsl:attribute>
                <xsl:attribute name="code"><xsl:value-of select="$workMobileType"/></xsl:attribute>
                <xsl:value-of select="$mobile"/>
              </csd:codedType></csd:contactPoint>
            </xsl:if>
            <xsl:if test="$telephone !=''">
              <csd:contactPoint><csd:codedType>
                <xsl:attribute name='codingScheme'><xsl:value-of select="$workContactPointScheme"/></xsl:attribute>
                <xsl:attribute name="code"><xsl:value-of select="$workPhoneType"/></xsl:attribute>
                <xsl:value-of select="$telephone"/>
              </csd:codedType></csd:contactPoint>
            </xsl:if>
            <xsl:if test="$alt_telephone !=''">
              <csd:contactPoint><csd:codedType>
                <xsl:attribute name='codingScheme'><xsl:value-of select="$workContactPointScheme"/></xsl:attribute>
                <xsl:attribute name="code"><xsl:value-of select="$workPhoneType"/></xsl:attribute>
                <xsl:value-of select="$alt_telephone"/>
              </csd:codedType></csd:contactPoint>
            </xsl:if>
            <xsl:if test="$fax != ''">
              <csd:contactPoint><csd:codedType>
                <xsl:attribute name='codingScheme'><xsl:value-of select="$workContactPointScheme"/></xsl:attribute>
                <xsl:attribute name="code"><xsl:value-of select="$workFaxType"/></xsl:attribute>
                <xsl:value-of select="$fax"/>
              </csd:codedType></csd:contactPoint>
            </xsl:if>
            <xsl:if test="$email != ''">
              <csd:contactPoint><csd:codedType>
                <xsl:attribute name='codingScheme'><xsl:value-of select="$workContactPointScheme"/></xsl:attribute>
                <xsl:attribute name="code"><xsl:value-of select="$workEmailType"/></xsl:attribute>
                <xsl:value-of select="$email"/>
              </csd:codedType></csd:contactPoint>
            </xsl:if>
            <xsl:if test="$workAddress !=''">
              <csd:address><csd:codedType>
                <xsl:attribute name='type'><xsl:value-of select='$workAddressType'/></xsl:attribute>
                <csd:addressLine component='all'><xsl:value-of select="$workAddress"/></csd:addressLine>
              </csd:codedType></csd:address>
            </xsl:if>


	    <xsl:choose>
	      <xsl:when test="$county != ''">
		<csd:organizations>
		  <csd:organization>
                    <xsl:attribute name="entityID">urn:uuid:<xsl:value-of select="$county"/></xsl:attribute>
		  </csd:organization>
		</csd:organizations>
	      </xsl:when>
	      <xsl:when test="$district != ''">
		<csd:organizations>
		  <csd:organization>
                    <xsl:attribute name="entityID">urn:uuid:<xsl:value-of select="$district"/></xsl:attribute>
		  </csd:organization>
		</csd:organizations>
	      </xsl:when>
	      <xsl:otherwise/>
	    </xsl:choose>

	    <csd:record>
	      <xsl:attribute name='created'><xsl:value-of select="$created"/></xsl:attribute>
	      <xsl:attribute name='updated'><xsl:value-of select="$modified"/></xsl:attribute>
	      <xsl:attribute name='sourceDirectory'><xsl:value-of select="$sourceDirectory"/></xsl:attribute>
	      <xsl:attribute name='status'>
		<xsl:choose>
		  <xsl:when test="$hidden = '1'">106-002</xsl:when>
		  <xsl:otherwise>106-001</xsl:otherwise>
		</xsl:choose>
	      </xsl:attribute>
	    </csd:record>


          </csd:facility>
	</xsl:if>
      </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
