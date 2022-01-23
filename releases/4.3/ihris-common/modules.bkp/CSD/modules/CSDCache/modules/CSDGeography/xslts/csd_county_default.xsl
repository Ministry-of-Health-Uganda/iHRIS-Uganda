<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:csd="urn:ihe:iti:csd:2013"
    exclude-result-prefixes="xs" version="1.0">

    <xsl:param name="rootProviderURN"/>
    <xsl:param name="rootFacilityURN"/>
    <xsl:param name="rootOrganizationURN"/>
    <xsl:param name="sourceDirectory"/>
    <xsl:param name="currentDateTime"/>

    <xsl:variable name="orgtype">COUNTY</xsl:variable> <!-- this should be replaces by a standardized list at some point, but what? -->
        
    <xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes"  omit-xml-declaration="yes"/>
    
    <xsl:template match="/">
      <xsl:for-each
          select="/relationship | /relationshipCollection/relationship">
        <xsl:variable name="organizationURN">urn:uuid:<xsl:value-of select="normalize-space(form[@name='county']/field[@name='csd_uuid']/text())"/></xsl:variable>

        <xsl:variable name="created"><xsl:value-of select="translate(normalize-space(form[@name='region']/@created),' ','T')"/></xsl:variable>
        <xsl:variable name="modified"><xsl:value-of select="$currentDateTime"/></xsl:variable>
        <xsl:variable name="name" select="normalize-space(form[@name='county']/field[@name='name']/text())"/>
        <xsl:variable name="hidden" select="normalize-space(form[@name='county']/field[@name='i2ce_hidden']/text())"/>
        <xsl:variable name="district" select="joinedForms/joinedForm[@report_form_name='district']/form[@name='district']/field[@name='csd_uuid']/text()"/>

        <xsl:if test="$name != '' and $orgtype != ''">
	  <csd:organization>                           
            <xsl:attribute name="entityID"><xsl:value-of select="$organizationURN"/></xsl:attribute>
	    <csd:codedType>
	      <xsl:attribute name='code'><xsl:value-of select="$orgtype"/></xsl:attribute>
              <xsl:attribute name="codingScheme"><xsl:value-of select="$rootOrganizationURN"/>:types</xsl:attribute>
	    </csd:codedType>
	    <csd:primaryName><xsl:value-of select="$name"/></csd:primaryName>
	    
	    <xsl:if test="$district != ''">
	      <csd:parent>
                  <xsl:attribute name="entityID">urn:uuid:<xsl:value-of select="$district"/></xsl:attribute>
	      </csd:parent>
	    </xsl:if>

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


          </csd:organization>
	</xsl:if>	
      </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
